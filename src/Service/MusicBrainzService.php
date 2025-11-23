<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzService
{
    public function __construct(private HttpClientInterface $client) {}

    public function getArtistData(string $mbid): array
    {
        try {
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query' => ['inc' => 'releases+artist-rels', 'fmt' => 'json'],
            ]);

            $data = $response->toArray();

            $artist = [
                'name' => $data['name'] ?? '',
                'genre' => $data['type'] ?? '',
                'country' => $data['area']['name'] ?? '',
                'foundedYear' => isset($data['life-span']['begin']) ? (int) substr($data['life-span']['begin'], 0, 4) : null,
                'albums' => [],
                'members' => [],
            ];

            foreach ($data['releases'] ?? [] as $release) {
                $artist['albums'][] = $release['title'];
            }

            foreach ($data['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $artist['members'][] = $rel['artist']['name'];
                }
            }

            return $artist;
        } catch (\Exception $e) {
            return [
                'name' => '',
                'genre' => '',
                'country' => '',
                'foundedYear' => null,
                'albums' => [],
                'members' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
