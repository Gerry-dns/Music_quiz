<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzService
{
    public function __construct(private HttpClientInterface $client) {}

    /**
     * Recherche un artiste par nom et renvoie son MBID principal.
     */
    public function searchArtistByName(string $name): ?string
    {
        $response = $this->client->request('GET', 'https://musicbrainz.org/ws/2/artist', [
            'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
            'query' => [
                'query' => $name,
                'fmt' => 'json',
                'limit' => 5, // limiter les résultats pour éviter surcharge
            ],
        ]);

        $data = $response->toArray();
        return $data['artists'][0]['id'] ?? null;
    }

    /**
     * Récupère les données détaillées d'un artiste via son MBID.
     */
    public function getArtistData(string $mbid): array
    {
        try {
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query' => [
                    'inc' => 'releases+artist-rels+tags+genres+recordings+aliases+annotation',
                    'fmt' => 'json',
                ],
            ]);

            $data = $response->toArray();

            // Genres : principal et sous-genres
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

            // Albums
            $albums = [];
            foreach ($data['releases'] ?? [] as $release) {
                $albums[] = $release['title'];
            }

            // Membres et instruments
            $members = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $members[] = [
                        'name' => $rel['artist']['name'],
                        'instruments' => is_array($rel['attribute-list'] ?? null) ? $rel['attribute-list'] : [],
                    ];
                }
            }

            return [
                'mbid' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'mainGenre' => $mainGenre,
                'subGenres' => $subGenres,
                'country' => $data['area']['name'] ?? '',
                'foundedYear' => isset($data['life-span']['begin']) ? (int) substr($data['life-span']['begin'], 0, 4) : null,
                'albums' => $albums,
                'members' => $members,
                'aliases' => array_map(fn($a) => $a['name'], $data['aliases'] ?? []),
                'annotation' => $data['annotation'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'mbid' => '',
                'name' => '',
                'mainGenre' => '',
                'subGenres' => [],
                'country' => '',
                'foundedYear' => null,
                'albums' => [],
                'members' => [],
                'aliases' => [],
                'annotation' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
