<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpFallbackService
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    /**
     * Effectue une requête GET sécurisée avec retry et fallback vers curl si nécessaire.
     *
     * @param string $url
     * @param array $query
     * @param int $retry
     * @return array|null
     */
    public function safeGet(string $url, array $query = [], int $retry = 3): ?array
    {
        while ($retry > 0) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                    'query' => $query,
                    'http_version' => '1.1',
                    'timeout' => 20,
                ]);

                return $response->toArray();
            } catch (\Throwable $e) {
                $retry--;
                sleep(2);
                if ($retry === 0) {
                    return $this->curlFallback($url, $query);
                }
            }
        }

        return null;
    }

    /**
     * Fallback vers curl si HttpClient échoue
     */
    private function curlFallback(string $url, array $query): ?array
    {
        $fullUrl = $url . '?' . http_build_query($query);
        $cmd = "curl -s -A 'MusicQuiz/1.0' " . escapeshellarg($fullUrl);
        $result = shell_exec($cmd);

        return $result ? json_decode($result, true) : null;
    }
}
