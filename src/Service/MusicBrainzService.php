<?php

namespace App\Service;

use App\Repository\CountryRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzService
{
    public function __construct(private HttpClientInterface $client, private CountryRepository $countryRepository) {}

    /**
     * Récupère toutes les données principales d'un artiste depuis MusicBrainz.
     *
     * @param string $mbid MBID de l'artiste
     * @return array Tableau avec albums, membres, genres, URLs, lifeSpan, beginArea, country
     */
    public function getArtistFullData(string $mbid): array
    {
        $artistData = [
            'mbid' => '',
            'name' => '',
            'albums' => [],
            'members' => [],
            'mainGenre' => '',
            'subGenres' => [],
            'aliases' => [],
            'lifeSpan' => [],
            'beginArea' => [],
            'country' => [],
            'urls' => [],
        ];



        try {
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query' => [
                    'inc' => 'release-groups+artist-rels+tags+genres+aliases+url-rels',
                    'fmt' => 'json',
                ],
            ]);

            $data = $response->toArray();
            // dd($data);

            // --- Genres ---
            $mainGenre = $data['type'] ?? ''; // fallback si pas de tags
            $subGenres = [];

            if (!empty($data['tags'])) {
                // trier par popularité (count)
                usort($data['tags'], fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));

                // le genre principal devient le plus populaire
                $mainGenre = $data['tags'][0]['name'] ?? $mainGenre;
                $mainGenreCount = $data['tags'][0]['count'] ?? 0;

                // remplir les sous-genres
                foreach ($data['tags'] as $tag) {
                    $tagName = $tag['name'] ?? '';
                    $tagCount = $tag['count'] ?? 0;

                    if ($tagName && $tagName !== $mainGenre) {
                        $subGenres[] = [
                            'name' => $tagName,
                            'count' => $tagCount
                        ];
                    }
                }
            }

            // résultat final structuré
            $genresForDashboard = [
                'mainGenre' => [
                    'name' => $mainGenre,
                    'count' => $mainGenreCount ?? 0,
                ],
                'subGenres' => $subGenres
            ];


            // --- Albums (albums studio uniquement) ---
            $albums = [];

            foreach ($data['release-groups'] ?? [] as $release) {

                // On ne garde que les albums
                if (($release['primary-type'] ?? '') !== 'Album') {
                    continue;
                }

                // On exclut compilations, lives, remasters, box sets, etc.
                if (!empty($release['secondary-types'])) {
                    continue;
                }

                // Sécurité minimale
                if (empty($release['id']) || empty($release['title'])) {
                    continue;
                }

                $albums[$release['id']] = [
                    'id' => $release['id'],
                    'title' => $release['title'],
                    'firstReleaseDate' => $release['first-release-date'] ?? null,
                ];
            }

            // ré-indexer proprement
            $albums = array_values($albums);


            // --- Membres ---
            $members = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $members[] = [
                        'name' => $rel['artist']['name'],
                        'begin' => $rel['begin'] ?? null,
                        'end' => $rel['end'] ?? null,
                        'instruments' => $rel['attributes'] ?? [], // récupère les instruments
                    ];
                }
            }



            // --- URLs structurées ---
            $urls = [
                'official' => [],
                'wikipedia' => [],
                'wikidata' => null,
                'social' => [],
            ];

            foreach ($data['relations'] ?? [] as $rel) {
                if (empty($rel['url']['resource'])) {
                    continue;
                }

                $type = strtolower($rel['type']);
                $url  = $rel['url']['resource'];

                switch ($type) {
                    case 'wikipedia':
                        // ex: https://en.wikipedia.org/wiki/Radiohead
                        $urls['wikipedia'][] = $url;
                        break;

                    case 'wikidata':
                        // ex: https://www.wikidata.org/wiki/Q12345
                        $urls['wikidata'] = $url;
                        break;

                    case 'official homepage':
                        $urls['official'][] = $url;
                        break;

                    default:
                        // réseaux sociaux, autres ressources
                        $urls['social'][$type][] = $url;
                }
            }
            // --- Alias ---
            $aliases = array_map(fn($a) => $a['name'], $data['aliases'] ?? []);

            // --- Life span ---
            $lifeSpan = [
                'begin' => $data['life-span']['begin'] ?? null,
                'ended' => $data['life-span']['ended'] ?? false,
                'end'   => $data['life-span']['end'] ?? null,
            ];

            // --- Begin area et pays ---
            $beginArea = $data['begin-area']['name'] ?? null;


            $countryCode = $data['country'] ?? null;
            $country = null;

            if ($countryCode) {
                try {
                    $country = Countries::getName($countryCode);
                } catch (\Symfony\Component\Intl\Exception\MissingResourceException $e) {
                    $country = null; // code pays inconnu
                }
            }

            // --- Fusionner toutes les données ---
            $artistData = array_merge($artistData, [
                'mbid' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'albums' => $albums,
                'members' => $members,
                'mainGenre' => $mainGenre,
                'subGenres' => $subGenres,
                'aliases' => $aliases,
                'lifeSpan' => $lifeSpan,
                'beginArea' => $beginArea,
                'country' => $country,
                'urls' => $urls,
                'summary'    => null,
                'image'      => null,
            ]);
            // dd($artistData);
        } catch (\Exception $e) {
            $artistData['error'] = $e->getMessage();
        }

        return $artistData;
    }
}
