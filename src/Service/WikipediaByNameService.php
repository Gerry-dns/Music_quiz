<?php

namespace App\Service;

class WikipediaByNameService
{
    private string $userAgent = 'MusicQuiz/1.0';

    /**
     * Récupère le texte complet et l'image principale d'un artiste à partir de son nom
     * (gère les homonymes : Prince, Kiss, Phoenix, etc.)
     */
    public function fetchFullArticleByName(string $artistName): ?array
    {
        if (!$artistName) {
            return null;
        }

        // 🔎 Résolution du vrai titre Wikipédia (anti-homonymes)
        $title =
            $this->resolveWikipediaTitle($artistName, 'fr')
            ?? $this->resolveWikipediaTitle($artistName, 'en')
            ?? str_replace(' ', '_', trim($artistName));

        // 1️⃣ Essai FR
        $urlFr = "https://fr.wikipedia.org/w/api.php?" . http_build_query([
            'action' => 'query',
            'prop' => 'extracts|pageimages|info|pageprops',
            'explaintext' => 'true',
            'titles' => $title,
            'format' => 'json',
            'pithumbsize' => 500,
            'inprop' => 'url',
        ]);

        $data = $this->fetchFromUrl($urlFr);
        if ($data) {
            $data['lang'] = 'fr';
            return $data;
        }

        // 2️⃣ Fallback EN
        $urlEn = "https://en.wikipedia.org/w/api.php?" . http_build_query([
            'action' => 'query',
            'prop' => 'extracts|pageimages|info|pageprops',
            'explaintext' => 'true',
            'titles' => $title,
            'format' => 'json',
            'pithumbsize' => 500,
            'inprop' => 'url',
        ]);

        $data = $this->fetchFromUrl($urlEn);
        if ($data) {
            $data['lang'] = 'en';
        }

        return $data;
    }

    /**
     * Résout un nom ambigu (Prince, Kiss…) vers le bon titre Wikipédia musical
     */
    public function resolveWikipediaTitle(string $name, string $lang = 'fr'): ?string
    {
        $url = "https://{$lang}.wikipedia.org/w/api.php?" . http_build_query([
            'action' => 'query',
            'list' => 'search',
            'srsearch' => $name,
            'format' => 'json',
        ]);

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
        if (empty($data['query']['search'])) {
            return null;
        }

        // 🎵 Priorité aux musiciens / groupes
        foreach ($data['query']['search'] as $result) {
            $snippet = strtolower(strip_tags($result['snippet']));
            if (
                str_contains($snippet, 'musicien') ||
                str_contains($snippet, 'chanteur') ||
                str_contains($snippet, 'groupe') ||
                str_contains($snippet, 'music')
            ) {
                return $result['title'];
            }
        }

        // sinon premier résultat
        return $data['query']['search'][0]['title'] ?? null;
    }

    /**
     * Récupère le résumé Wikipédia (court)
     */
    public function fetchSummaryByName(string $artistName): ?array
    {
        if (!$artistName) {
            return null;
        }

        $title =
            $this->resolveWikipediaTitle($artistName, 'fr')
            ?? $this->resolveWikipediaTitle($artistName, 'en')
            ?? str_replace(' ', '_', trim($artistName));

        // FR
        $urlFr = "https://fr.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        $summary = $this->fetchSummaryFromUrl($urlFr);
        if ($summary) {
            return $summary;
        }

        // EN
        $urlEn = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        return $this->fetchSummaryFromUrl($urlEn);
    }

    /**
     * Appel REST résumé
     */
    public function fetchSummaryFromUrl(string $url): ?array
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
            'image' => $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null,
            'url' => $data['content_urls']['desktop']['page'] ?? null,
            'lang' => str_contains($url, 'fr.wikipedia') ? 'fr' : 'en',
        ];
    }

    /**
     * Appel MediaWiki texte complet (anti pages d’homonymie)
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
        if (!isset($data['query']['pages'])) {
            return null;
        }

        $page = reset($data['query']['pages']);

        // ❌ Ignore pages d’homonymie
        if (isset($page['pageprops']['disambiguation'])) {
            return null;
        }

        if (empty($page['extract'])) {
            return null;
        }

        return [
            'fullText' => $page['extract'],
            'image' => $page['thumbnail']['source'] ?? null,
            'url' => $page['fullurl'] ?? null,
        ];
    }

    /**
     * Récupère le résumé Wikipédia à partir d'une URL complète.
     */
    public function fetchSummaryByUrl(string $wikipediaUrl): ?array
    {
        if (!$wikipediaUrl) {
            return null;
        }

        // Détermine la langue (fr ou en) à partir de l’URL
        $lang = str_contains($wikipediaUrl, 'fr.wikipedia.org') ? 'fr' : 'en';

        // Récupère le titre depuis l’URL
        $title = basename(parse_url($wikipediaUrl, PHP_URL_PATH));

        // Prépare l’URL de l’API REST
        $apiUrl = "https://{$lang}.wikipedia.org/api/rest_v1/page/summary/" . $title;

        // Appelle la méthode existante pour récupérer le résumé
        return $this->fetchSummaryFromUrl($apiUrl);
    }
}
