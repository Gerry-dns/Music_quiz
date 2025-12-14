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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\MusicBrainzService;
use App\Service\ArtistPopulatorService;
use App\Service\QuizGeneratorService;
use App\Service\WikipediaByNameService;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private HttpClientInterface $client,
        private \App\Service\WikipediaByNameService $wiki,
        private QuizGeneratorService $quizGenerator,
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
            AssociationField::new('country', 'Pays')
                ->setFormTypeOption('choice_label', 'name'),

            TextField::new('beginArea', 'Ville de formation')->hideOnIndex(),
            ImageField::new('coverImage', 'Image')
                ->setBasePath('')
                ->onlyOnDetail(),
            ArrayField::new('biography', 'Biographie')->hideOnIndex(),
            // Bouton pour la bio complète (AJAX)



            ArrayField::new('albums', 'Albums')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('subGenres', 'Sous-genres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('members', 'Membres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            CollectionField::new('members', 'Membres')
                ->setEntryType(MemberType::class)
                ->allowAdd()
                ->allowDelete()
                ->onlyOnForms(),
            ArrayField::new('lifeSpan', 'Life Span')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/array_list.html.twig'),

        ];

        $artist = $this->getContext()?->getEntity()?->getInstance();
        if ($artist) {
            $bio = $artist->getBiography();
            // dd($bio);
        }
        if ($artist) {
            $urls = $artist->getUrls(); // toutes les URLs
            foreach ($urls as $platform => $link) {
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

    /**
     * Helper pour récupérer la valeur d'un champ depuis l'entité courante.
     */
    private function getEntityFieldValue(string $field)
    {
        $entity = $this->getContext()?->getEntity()?->getInstance();
        if (!$entity) return null;

        $getter = 'get' . ucfirst($field);
        return method_exists($entity, $getter) ? $entity->$getter() : null;
    }

    private function curlRequest(string $url, array $query): ?array
    {
        $fullUrl = $url . '?' . http_build_query($query);
        $cmd = "curl -s -A 'MusicQuiz/1.0' " . escapeshellarg($fullUrl);
        $result = shell_exec($cmd);
        return $result ? json_decode($result, true) : null;
    }

    private function safeRequest(string $url, array $query, int $retry = 3): ?array
    {
        while ($retry > 0) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                    'query' => $query,
                    'http_version' => '1.1',
                    'timeout' => 20,
                ]);
                return $response->toArray();
            } catch (\Throwable $e) {
                $retry--;
                sleep(2);
                if ($retry === 0) {
                    return $this->curlRequest($url, $query);
                }
            }
        }
        return null;
    }

    public function fetchFromMusicBrainz(
        EntityManagerInterface $em,
        MusicBrainzService $mbService,
        ArtistPopulatorService $populator,
        LoggerInterface $logger,
        WikipediaByNameService $wikiService,
    ): RedirectResponse {
        $artist = $this->getContext()->getEntity()->getInstance();
        $mbid = trim($artist->getMbid());

        // Vérification MBID
        if (!$mbid || !preg_match('/^[0-9a-fA-F-]{36}$/', $mbid)) {
            $this->addFlash('danger', 'MBID invalide');
            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'edit',
                'crudControllerFqcn' => self::class,
                'entityId' => $artist->getId(),
            ]));
        }

        try {
            // 1️⃣ Récupération des données MusicBrainz
            $data = $mbService->getArtistData($mbid);
            $populator->populateFromMusicBrainz($artist, $data);

            // Logging brut
            $logger->info('Données MusicBrainz récupérées', [
                'artistId' => $artist->getId(),
                'mbid' => $mbid,
                'data' => $data,
            ]);

            // 2️⃣ Récupérer les URLs existantes
            $urls = $artist->getUrls() ?? [];

            // 3️⃣ Ajouter les URLs depuis MusicBrainz
            foreach ($data['urls'] ?? [] as $type => $link) {
                if ($link) {
                    $key = strtolower(str_replace(' ', '_', $type));
                    $urls[$key] = $link;
                }
            }

            // 4️⃣ Ajouter le lien Wikipédia
            $summaryData = $this->wiki->fetchSummaryByName($artist->getName());
            if ($summaryData) {
                // Biographie (uniquement résumé)
                $artist->setBiography([
                    'source'  => 'wikipedia',
                    'summary' => $summaryData['summary'] ?? null,
                ]);

                // Couverture si image dispo
                if (!empty($summaryData['image'])) {
                    $artist->setCoverImage($summaryData['image']);
                }

                // Wikipedia dans urls
                $urls['wikipedia'] = 'https://' . ($summaryData['lang'] ?? 'fr') . '.wikipedia.org/wiki/' . str_replace(' ', '_', $artist->getName());
            }

            // 5️⃣ Mettre à jour l'entité avec toutes les URLs
            $artist->setUrls($urls);

            // 6️⃣ Sauvegarde en base
            $em->flush();

            $this->addFlash('success', sprintf(
                'Données récupérées depuis MusicBrainz + Wikipédia pour "%s".',
                $artist->getName()
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }


        return $this->redirect($this->generateUrl('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::class,
            'entityId' => $artist->getId(),
        ]));
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
            ->linkToUrl(
                fn(Artist $artist) => $this->generateUrl(
                    'artist_detail',      // nom de la route publique
                    ['id' => $artist->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            )
            ->addCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_EDIT, $fetch)
            ->add(Crud::PAGE_EDIT, $generateQuiz)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $viewOnSite); // <- corrigé ici
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

        // Redirection vers la page de détails de l’artiste
        return $this->redirect(
            $this->urlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($artist->getId())
                ->generateUrl()
        );
    }
}
