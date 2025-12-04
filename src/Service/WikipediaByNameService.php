<?php

namespace App\Service;

class WikipediaByNameService
{
    /**
     * Récupère le résumé Wikipédia d'un artiste à partir de son nom.
     */
    public function fetchSummaryByName(string $artistName): ?array
    {
        if (!$artistName) {
            return null;
        }

        // Transformer le nom en format "titre de page"
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

    private function fetchSummaryFromUrl(string $url):?array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: MusicQuiz/1.0\r\n",
                'timeout' => 10,
            ]
        ]);

        $json = @file_get_contents($url, false, $context);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);
         return [
        'summary' => $data['extract'] ?? null,
        'image' => $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null
    ];
}
}