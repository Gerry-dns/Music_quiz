<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use App\Repository\CountryRepository;
use App\Repository\GenreRepository;
use App\Repository\DecadeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtistController extends AbstractController
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private CountryRepository $countryRepository,
        private GenreRepository $genreRepository,
        private DecadeRepository $decadeRepository
    ) {}

    // =========================
    // Page détail d’un artiste
    // =========================
    #[Route('/artist/{id}', name: 'artist_detail')]
    public function show(Artist $artist): Response
    {
        return $this->render('artist/detail.html.twig', [
            'artist' => $artist,
        ]);
    }

    // =========================================
    // Liste des artistes avec filtres dropdown
    // =========================================
    #[Route('/artists', name: 'artist_list')]
    public function list(Request $request): Response
    {
        $countryId = $request->query->get('country');
        $genreId   = $request->query->get('genre');
        $decadeId  = $request->query->get('decade');
        $city      = $request->query->get('city'); // récupère le texte

        $countryId = $countryId !== null ? (int) $countryId : null;
        $genreId   = $genreId !== null ? (int) $genreId : null;
        $decadeId  = $decadeId !== null ? (int) $decadeId : null;

        $artists = $this->artistRepository->findWithFilters(
            country: $countryId,
            city: $city,
            decade: $decadeId,
            genre: $genreId
        );

        $countries = $this->countryRepository->findAll();
        $genres    = $this->genreRepository->findAll();
        $decades   = $this->decadeRepository->findAll();
        $cities    = $this->artistRepository->findDistinctBeginAreas(); // <-- ici

        return $this->render('artist/list.html.twig', [
            'artists'   => $artists,
            'filters'   => [
                'country' => $countryId,
                'genre'   => $genreId,
                'decade'  => $decadeId,
                'city'    => $city,
            ],
            'countries' => $countries,
            'genres'    => $genres,
            'decades'   => $decades,
            'cities'    => $cities, // <-- et ici pour Twig
        ]);
    }
}
