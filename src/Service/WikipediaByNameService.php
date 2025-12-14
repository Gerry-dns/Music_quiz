<?php

namespace App\Service;

class WikipediaByNameService
{
    private string $userAgent = 'MusicQuiz/1.0';

    /**
     * Récupère le texte complet et l'image principale d'un artiste à partir de son nom.
     */
    public function fetchFullArticleByName(string $artistName): ?array
    {
        if (!$artistName) {
            return null;
        }

        $title = str_replace(' ', '_', trim($artistName));

        // 1️⃣ Essai FR
        $urlFr = "https://fr.wikipedia.org/w/api.php?" . http_build_query([
            'action' => 'query',
            'prop' => 'extracts|pageimages|info',
            'explaintext' => 'true', // tout le texte
            'titles' => $title,
            'format' => 'json',
            'pithumbsize' => 500,
            'inprop' => 'url', // pour avoir le lien public
        ]);
        $data = $this->fetchFromUrl($urlFr);
        if ($data) return $data;

        // 2️⃣ Fallback EN
        $urlEn = "https://en.wikipedia.org/w/api.php?" . http_build_query([
            'action' => 'query',
            'prop' => 'extracts|pageimages|info',
            'explaintext' => 'true',
            'titles' => $title,
            'format' => 'json',
            'pithumbsize' => 500,
            'inprop' => 'url',
        ]);
        return $this->fetchFromUrl($urlEn);
    }

    /**
     * Récupère le résumé Wikipédia d'un artiste à partir de son nom.
     */
    public function fetchSummaryByName(string $artistName): ?array
    {
        if (!$artistName) {
            return null;
        }

        $title = str_replace(' ', '_', trim($artistName));

        // 1️⃣ Essai FR
        $urlFr = "https://fr.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        $summary = $this->fetchSummaryFromUrl($urlFr);
        if ($summary) {
            return $summary;
        }

        // 2️⃣ Fallback EN si rien en FR
        $urlEn = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        return $this->fetchSummaryFromUrl($urlEn);
    }

    /**
     * Méthode privée pour récupérer le résumé depuis une URL REST.
     */
    private function fetchSummaryFromUrl(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: {$this->userAgent}\r\n",
                'timeout' => 10,
            ]
        ]);

        $json = @file_get_contents($url, false, $context);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);
        if (empty($data['extract'])) {
            return null;
        }

        return [
            'summary' => $data['extract'],
            'image'   => $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null,
            'url'     => $data['content_urls']['desktop']['page'] ?? null,
            'lang'    => str_contains($url, 'fr.wikipedia') ? 'fr' : 'en',
        ];
    }

    /**
     * Méthode privée pour récupérer le texte complet depuis l'API MediaWiki.
     */
    private function fetchFromUrl(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: {$this->userAgent}\r\n",
                'timeout' => 10,
            ]
        ]);

        $json = @file_get_contents($url, false, $context);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        // La réponse de MediaWiki est un objet 'query' contenant les pages
        if (!isset($data['query']['pages'])) {
            return null;
        }

        $page = reset($data['query']['pages']); // récupère la première page
        return [
            'fullText' => $page['extract'] ?? null,
            'image'    => $page['thumbnail']['source'] ?? null,
            'url'      => $page['fullurl'] ?? null,
        ];
    }
}
