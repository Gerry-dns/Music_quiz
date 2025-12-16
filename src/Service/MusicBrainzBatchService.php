<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzBatchService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    // Méthode pour faire une requête sécurisée avec User-Agent et fallback
    private function safeRequest(string $url, array $query = []): ?array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0 (gerryneverstops@proton.me)'],
                'query'   => $query,
                'http_version' => '1.1',
                'timeout' => 20,
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            // tu peux logger l'erreur ici
            return null;
        }
    }

    /**
     * Récupère plusieurs artistes par MBID en respectant le rate limit (1 req/sec)
     *
     * @param string[] $mbids
     * @return array
     */
    public function fetchArtistsByMbids(array $mbids): array
    {
        $results = [];
        foreach ($mbids as $mbid) {
            $artistData = $this->safeRequest(
                "https://musicbrainz.org/ws/2/artist/{$mbid}",
                [
                    'inc' => 'releases+aliases+annotation+genres+tags+artist-rels+url-rels+release-groups',
                    'fmt' => 'json'
                ]
            );

            $results[$mbid] = $artistData;

            // Respecte 1 requête par seconde
            sleep(1);
        }

        return $results;
    }
}
