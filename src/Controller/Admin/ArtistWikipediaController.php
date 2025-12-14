<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Artist;
use App\Service\WikipediaByNameService;

class ArtistWikipediaController extends AbstractController
{
    public function __construct(private WikipediaByNameService $wiki) {}

    #[Route('/artist/{id}/biography-full', name: 'artist_biography_full')]
    public function fetchFullBiography(Artist $artist): JsonResponse
    {
        if (!$artist) {
            return new JsonResponse(['content' => null]);
        }

        $pageTitle = $artist->getName();

        // Cache 24h
        $cache = new FilesystemAdapter();

        $fullText = $cache->get('wiki_full_' . md5($pageTitle), function (ItemInterface $item) use ($pageTitle) {
            $item->expiresAfter(86400); // 24h
            $data = $this->wiki->fetchFullArticleByName($pageTitle);
            return $data['fullText'] ?? null;
        });

        return new JsonResponse([
            'content' => $fullText,
            'source'  => 'Wikipedia',
            'url'     => 'https://fr.wikipedia.org/wiki/' . urlencode(str_replace(' ', '_', $pageTitle)),
        ]);
    }
}
