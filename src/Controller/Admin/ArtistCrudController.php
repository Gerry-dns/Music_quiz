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

    /**
     * Helper pour récupérer le label d'un item Wikidata via l'API JSON.
     */
    private function fetchWikidataLabel(string $itemId, string $lang = 'en'): ?string
    {
        $url = "https://www.wikidata.org/wiki/Special:EntityData/{$itemId}.json";
        try {
            $response = file_get_contents($url);
            if (!$response) return null;
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            return $data['entities'][$itemId]['labels'][$lang]['value'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchMembersFromWikidataSPARQL(string $wikidataId): array
    {
        $endpoint = 'https://query.wikidata.org/sparql';

        $query = <<<SPARQL
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?memberLabel ?instrumentLabel WHERE {
    wd:$wikidataId wdt:P527 ?member .
    OPTIONAL { ?member wdt:P1303 ?instrument . }
    SERVICE wikibase:label { bd:serviceParam wikibase:language "fr,en". }
}
SPARQL;

        $response = $this->client->request('GET', $endpoint, [
            'headers' => ['Accept' => 'application/sparql-results+json'],
            'query' => ['query' => $query],
        ]);

        $data = $response->toArray();

        $result = [];

        foreach ($data['results']['bindings'] as $row) {
            $name = $row['memberLabel']['value'] ?? null;
            $instrument = $row['instrumentLabel']['value'] ?? null;

            if ($name) {
                // Initialise le tableau instruments si besoin
                if (!isset($result[$name])) {
                    $result[$name] = [];
                }
                // Ajoute l'instrument s'il existe et n'est pas déjà présent
                if ($instrument && !in_array($instrument, $result[$name])) {
                    $result[$name][] = $instrument;
                }
            }
        }

        return $result;
    }

    private array $instrumentMap = [
        'guitare' => ['guitare', 'guitare basse', 'guitare électrique', 'guitare acoustique'],
        'batterie' => ['batterie', 'percussions'],
        'voix' => ['voix', 'chant', 'harmonica'],
        'clavier' => ['clavier', 'piano', 'orgue', 'instrument à clavier'],
    ];

    private function getMainInstrument(string $instr): ?string
    {
        $instrLower = strtolower($instr);
        foreach ($this->instrumentMap as $main => $variants) {
            foreach ($variants as $variant) {
                if (str_contains($instrLower, strtolower($variant))) {
                    return $main;
                }
            }
        }
        return null;
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

            // 2️⃣ Extraire l'URL Wikidata si elle existe
            $wikidataUrl = $artist->getUrls()['wikidata'] ?? null;
            $wikidataId = $wikidataUrl ? basename($wikidataUrl) : null;

            // 3️⃣ Récupérer les membres existants
            $members = $artist->getMembers() ?? [];
            $existingNames = array_column($members, 'name');

            if ($wikidataId) {
                // 3a️⃣ Récupération des membres depuis Wikidata
                $wikidataMembers = $this->fetchMembersFromWikidataSPARQL($wikidataId);

                // 3b️⃣ Traitement pour obtenir type principal et détails
                $membersProcessed = [];
                foreach ($wikidataMembers as $name => $instruments) {
                    $main = [];
                    foreach ($instruments as $instr) {
                        $mainType = $this->getMainInstrument($instr);
                        if ($mainType) $main[$mainType] = true;
                    }
                    $membersProcessed[$name] = [
                        'main' => array_keys($main), // types principaux
                        'details' => $instruments    // instruments complets
                    ];
                }

                // 3c️⃣ Fusionner les instruments et types principaux pour les membres existants
                foreach ($members as &$member) {
                    $name = $member['name'];
                    if (isset($membersProcessed[$name])) {
                        $member['instruments'] = array_unique(array_merge(
                            $member['instruments'] ?? [],
                            $membersProcessed[$name]['details']
                        ));
                        $member['mainInstruments'] = $membersProcessed[$name]['main'];
                    }
                }

                // 3d️⃣ Ajouter les membres présents uniquement dans Wikidata
                foreach ($membersProcessed as $name => $info) {
                    if (!in_array($name, $existingNames)) {
                        $members[] = [
                            'name' => $name,
                            'instruments' => $info['details'],
                            'mainInstruments' => $info['main']
                        ];
                    }
                }

                $artist->setMembers($members);
            }

            // 4️⃣ Populer l'artiste avec MusicBrainz
            $populator->populateFromMusicBrainz($artist, $data);

            // 5️⃣ Logging
            $logger->info('Données MusicBrainz récupérées', [
                'artistId' => $artist->getId(),
                'mbid' => $mbid,
                'data' => $data,
            ]);

            // 6️⃣ Récupérer et fusionner les URLs
            $urls = $artist->getUrls() ?? [];
            foreach ($data['urls'] ?? [] as $type => $link) {
                if ($link) {
                    $key = strtolower(str_replace(' ', '_', $type));
                    $urls[$key] = $link;
                }
            }

            // 7️⃣ Ajouter Wikipédia
            $summaryData = $this->wiki->fetchSummaryByName($artist->getName());
            if ($summaryData) {
                $artist->setBiography([
                    'source' => 'wikipedia',
                    'summary' => $summaryData['summary'] ?? null,
                ]);

                if (!empty($summaryData['image'])) {
                    $artist->setCoverImage($summaryData['image']);
                }

                $urls['wikipedia'] = 'https://' . ($summaryData['lang'] ?? 'fr') . '.wikipedia.org/wiki/' . str_replace(' ', '_', $artist->getName());
            }

            $artist->setUrls($urls);

            // 8️⃣ Sauvegarde en base
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
