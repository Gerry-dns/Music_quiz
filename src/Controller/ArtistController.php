<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Service\WikipediaByNameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtistController extends AbstractController
{
    public function __construct(private WikipediaByNameService $wiki) {}

    #[Route('/artist/{id}', name: 'artist_detail')]
    public function show(Artist $artist): Response
    {
        

        return $this->render('artist/detail.html.twig', [
            'artist' => $artist,
        ]);
    }
}
