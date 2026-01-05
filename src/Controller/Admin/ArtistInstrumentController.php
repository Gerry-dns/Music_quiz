<?php

namespace App\Controller\Admin;

use App\Entity\ArtistInstrument;
use App\Entity\Instrument;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artist-instrument')]
class ArtistInstrumentController extends AbstractController
{
    public function __construct(
        private ArtistRepository $artistRepository,
    ) {}

    #[Route('/add/{artistId}', name: 'artist_instrument_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em, int $artistId): Response
    {
        $artist = $this->artistRepository->find($artistId);
        $instrumentId = (int) $request->request->get('instrument_id');

        if (!$artist || !$instrumentId) {
            $this->addFlash('error', 'Artiste ou instrument invalide.');
            return $this->redirectToRoute('artist_detail', ['id' => $artistId]);
        }

        $instrument = $em->getRepository(Instrument::class)->find($instrumentId);
        if (!$instrument) {
            $this->addFlash('error', 'Instrument introuvable.');
            return $this->redirectToRoute('artist_detail', ['id' => $artistId]);
        }

        // Vérifier si le lien existe déjà pour éviter les doublons
        $existing = $em->getRepository(ArtistInstrument::class)
            ->findOneBy(['artist' => $artist, 'instrument' => $instrument]);

        if ($existing) {
            $this->addFlash('info', 'Cet instrument est déjà associé à l’artiste.');
            return $this->redirectToRoute('artist_detail', ['id' => $artistId]);
        }

        // Création de la relation
        $artistInstrument = new ArtistInstrument();
        $artistInstrument->setArtist($artist);
        $artistInstrument->setInstrument($instrument);

        $em->persist($artistInstrument);
        $em->flush();

        $this->addFlash('success', 'Instrument ajouté avec succès.');
        return $this->redirectToRoute('artist_detail', ['id' => $artistId]);
    }
}
