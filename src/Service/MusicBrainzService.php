<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzService
{
    public function __construct(private HttpClientInterface $client) {}

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
            'beginArea' => null,
            'country' => null,
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


            // --- Albums ---
            $albums = [];
            foreach ($data['release-groups'] ?? [] as $release) {
                if (($release['primary-type'] ?? '') === 'Album') {
                    $albums[$release['id']] = [
                        'id' => $release['id'] ?? null,
                        'title' => $release['title'] ?? '',
                        'firstReleaseDate' => $release['first-release-date'] ?? null,
                    ];
                }
            }
            $albums = array_values($albums); // ré-indexer

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

           

            // --- URLs ---
            $urls = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (!empty($rel['url']['resource'])) {
                    $key = strtolower(str_replace(' ', '_', $rel['type']));
                    $urls[$key] = $rel['url']['resource'];
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
            $country = $data['area']['name'] ?? null;

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
            ]);
            // dd($artistData);
        } catch (\Exception $e) {
            $artistData['error'] = $e->getMessage();
        }

        return $artistData;
    }
}
