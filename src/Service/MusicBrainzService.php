<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzService
{
    public function __construct(private HttpClientInterface $client) {}

    /**
     * Récupère les données principales d'un artiste avec releases et URLs.
     */
    // src/Service/MusicBrainzService.php
    public function getArtistFullData(string $mbid): array
    {
        $artistData = [
            'albums' => [],
            'urls' => [],
            'members' => [],
            'mainGenre' => '',
            'subGenres' => [],
            'aliases' => [],
            'lifeSpan' => [],
            'beginArea' => null,
            'country' => null,
        ];

        try {
            // Récupération de l'artiste avec les relations et releases
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query' => [
                    'inc' => 'release-groups+artist-rels+tags+genres+aliases+url-rels',
                    'fmt' => 'json',
                ],
            ]);

            $data = $response->toArray();



            // Genres
            $mainGenre = $data['type'] ?? '';
            $subGenres = [];
            if (!empty($data['tags'])) {
                usort($data['tags'], fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                $mainGenre = $data['tags'][0]['name'] ?? $mainGenre;
                foreach ($data['tags'] as $tag) {
                    if (($tag['name'] ?? '') !== $mainGenre) {
                        $subGenres[] = $tag['name'];
                    }
                }
            }

            // Rel
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
            $albums = array_values($albums); // Pour ré-indexer le tableau


            // Membres
            $members = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $members[] = [
                        'name' => $rel['artist']['name'],
                        'instruments' => is_array($rel['attribute-list'] ?? null) ? $rel['attribute-list'] : [],
                    ];
                }
            }

            // URLs
            $urls = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (!empty($rel['url']['resource'])) {
                    $key = strtolower(str_replace(' ', '_', $rel['type']));
                    $urls[$key] = $rel['url']['resource'];
                }
            }

            $artistData = array_merge($artistData, [
                'mbid' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'mainGenre' => $mainGenre,
                'subGenres' => $subGenres,
                'albums' => $albums,
                'members' => $members,
                'aliases' => array_map(fn($a) => $a['name'], $data['aliases'] ?? []),
                'lifeSpan' => [
                    'begin' => $data['life-span']['begin'] ?? null,
                    'ended' => $data['life-span']['ended'] ?? false,
                    'end'   => $data['life-span']['end'] ?? null,
                ],
                'beginArea' => $data['begin-area']['name'] ?? null,
                'country' => $data['area']['name'] ?? null,
                'urls' => $urls,
            ]);
        } catch (\Exception $e) {
            $artistData['error'] = $e->getMessage();
        }

        return $artistData;
    }
}
