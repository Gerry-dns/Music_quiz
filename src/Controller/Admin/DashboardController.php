<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Questions;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MusicBrainzService;
use App\Entity\ArtistMemberInstrument;


class DashboardController extends AbstractDashboardController
{
    private MusicBrainzService $mbService;
    private EntityManagerInterface $em;

    public function __construct(MusicBrainzService $mbService, EntityManagerInterface $em)
    {
        $this->mbService = $mbService;
        $this->em = $em;
    }

    // DashboardController.php
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $artists = $this->em->getRepository(Artist::class)->findRandomArtists(1);
        $artist = $artists[0] ?? null;

        if ($artist) {
            $uniqueAlbums = [];

            foreach ($artist->getAlbums() as $album) {
                if (!$album->getId()) {
                    continue;
                }
                $uniqueAlbums[$album->getId()] = $album;
            }

            // On réinitialise les albums
            foreach ($artist->getAlbums() as $existingAlbum) {
                $artist->removeAlbum($existingAlbum);
            }

            // On ajoute les albums uniques
            foreach ($uniqueAlbums as $albumObj) {
                $artist->addAlbum($albumObj);
            }
        }



        $question = $this->em->getRepository(Questions::class)
            ->createQueryBuilder('q')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('admin/welcome.html.twig', [
            'question' => $question,
            'artist' => $artist,
        ]);
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Music Quiz Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Accueil', 'fa fa-home');
        yield MenuItem::linkToCrud('Artistes', 'fas fa-users', Artist::class);
        yield MenuItem::linkToCrud('Questions', 'fas fa-question', Questions::class);
        yield MenuItem::linkToRoute('Tester le Quiz', 'fas fa-flask', 'admin_quiz_test');
        yield MenuItem::linkToCrud('Membres du groupe et leurs instruments', 'fas fa-music', ArtistMemberInstrument::class);
    }

    #[Route('/admin/quiz_test', name: 'admin_quiz_test')]
    public function quizTest(): Response
    {
        $questions = $this->em->getRepository(Questions::class)
            ->createQueryBuilder('q')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/quiz_test.html.twig', [
            'questions' => $questions
        ]);
    }
}
