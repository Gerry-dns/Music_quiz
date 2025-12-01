<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Questions;
use App\Service\MusicBrainzService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private MusicBrainzService $mbService;
    private EntityManagerInterface $em;

    public function __construct(MusicBrainzService $mbService, EntityManagerInterface $em)
    {
        $this->mbService = $mbService;
        $this->em = $em;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // On récupère tous les artistes depuis la base
        $artists = $this->em->getRepository(Artist::class)->findAll();

        $artistsData = [];
        foreach ($artists as $artist) {
            if ($artist->getMbid()) {
                $artistsData[] = $this->mbService->getArtistData($artist->getMbid());
            }
        }

        return $this->render('admin/welcome.html.twig', [
            'artists' => $artistsData
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
    }
}
