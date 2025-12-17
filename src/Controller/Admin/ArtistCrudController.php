<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Form\MemberType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use App\Service\MusicBrainzService;
use App\Service\ArtistPopulatorService;
use App\Service\WikipediaByNameService;
use App\Service\QuizGeneratorService;
use App\Service\HttpFallbackService;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private MusicBrainzService $mbService,
        private ArtistPopulatorService $populator,
        private WikipediaByNameService $wikiService,
        private QuizGeneratorService $quizGenerator,
        private HttpFallbackService $httpFallback,
        private AdminUrlGenerator $urlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name', 'Nom'),
            TextField::new('mbid', 'MBID')->hideOnIndex(),
            AssociationField::new('mainGenre', 'Genre principal'),
            AssociationField::new('country', 'Pays')->setFormTypeOption('choice_label', 'name'),
            TextField::new('beginArea', 'Ville de formation'),
            ImageField::new('coverImage', 'Image')->setBasePath('')->onlyOnDetail(),
            ArrayField::new('biography', 'Biographie')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('albums', 'Albums')->onlyOnDetail()->hideOnIndex()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('subGenres', 'Sous-genres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('members', 'Membres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            CollectionField::new('members', 'Membres')->setEntryType(MemberType::class)->allowAdd()->allowDelete()->onlyOnForms(),
            ArrayField::new('lifeSpan', 'Life Span')->hideOnIndex()->setTemplatePath('admin/fields/array_list.html.twig'),
        ];

        $artist = $this->getContext()?->getEntity()?->getInstance();
        if ($artist) {
            foreach ($artist->getUrls() ?? [] as $platform => $link) {
                if ($link) {
                    $fields[] = TextField::new($platform)
                        ->onlyOnDetail()
                        ->formatValue(fn($v) => sprintf('<a href="%s" target="_blank">%s</a>', $v, $v))
                        ->setFormTypeOption('attr', ['class' => 'ea-link']);
                }
            }
        }

        return $fields;
    }

    public function fetchFromMusicBrainz(
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): RedirectResponse {
        /** @var Artist $artist */
        $artist = $this->getContext()->getEntity()->getInstance();
        if (!$artist) {
            $this->addFlash('danger', 'Artiste introuvable');
            return $this->redirect($this->urlGenerator->setAction(Action::INDEX)->generateUrl());
        }

        $mbid = trim($artist->getMbid());
        if (!$mbid || !preg_match('/^[0-9a-fA-F-]{36}$/', $mbid)) {
            $this->addFlash('danger', 'MBID invalide');
            return $this->redirect($this->urlGenerator->setAction(Action::EDIT)->setEntityId($artist->getId())->generateUrl());
        }

        try {
            // Récupération et traitement des données
            $data = $this->mbService->getArtistData($mbid);
            $this->populator->populateFromMusicBrainz($artist, $data);

            // Récupération des tracks depuis les releases
            // après avoir peuplé les albums
            $this->populator->populateTracksFromReleases($artist, $this->mbService);

            // Récupération Wikipédia
            $summaryData = $this->wikiService->fetchSummaryByName($artist->getName());
            if ($summaryData) {
                $artist->setBiography(['source' => 'wikipedia', 'summary' => $summaryData['summary'] ?? null]);
                if (!empty($summaryData['image'])) {
                    $artist->setCoverImage($summaryData['image']);
                }
            }

            // Sauvegarde en base
            $em->flush();

            $logger->info('Données MusicBrainz + Wikipédia récupérées', ['artistId' => $artist->getId(), 'mbid' => $mbid]);
            $this->addFlash('success', sprintf('Données récupérées pour "%s".', $artist->getName()));
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($this->urlGenerator->setAction(Action::EDIT)->setEntityId($artist->getId())->generateUrl());
    }

    public function generateQuiz(AdminContext $context): RedirectResponse
    {
        /** @var Artist $artist */
        $artist = $context->getEntity()->getInstance();
        if (!$artist) {
            $this->addFlash('danger', 'Artiste introuvable');
            return $this->redirect($this->urlGenerator->setAction(Action::INDEX)->generateUrl());
        }

        $this->quizGenerator->generateQuestionsForArtist($artist);
        $this->addFlash('success', 'Questions générées pour : ' . $artist->getName());

        return $this->redirect(
            $this->urlGenerator->setController(self::class)->setAction(Action::DETAIL)->setEntityId($artist->getId())->generateUrl()
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        $fetch = Action::new('fetchFromMusicBrainz', 'Récupérer depuis MusicBrainz', 'fa fa-sync')
            ->linkToCrudAction('fetchFromMusicBrainz')
            ->addCssClass('btn btn-success');

        $generateQuiz = Action::new('generateQuiz', 'Générer quiz', 'fa fa-magic')
            ->linkToCrudAction('generateQuiz')
            ->addCssClass('btn btn-primary');

        $viewOnSite = Action::new('viewOnSite', 'Voir sur site')
            ->linkToUrl(fn(Artist $artist) => $this->generateUrl('artist_detail', ['id' => $artist->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
            ->addCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_EDIT, $fetch)
            ->add(Crud::PAGE_EDIT, $generateQuiz)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $viewOnSite);
    }
}
