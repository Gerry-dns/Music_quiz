<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(private HttpClientInterface $client) {}

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            TextField::new('name', 'Nom'),
            TextField::new('mbid', 'MBID')->hideOnIndex(),
            TextField::new('mainGenre', 'Genre principal'),
            TextField::new('country', 'Pays'),
            IntegerField::new('foundedYear', 'Année de création'),
            TextareaField::new('biography', 'Biographie')->hideOnIndex(),

            // UrlField::new('coverImage', 'URL Pochette')
            //     ->hideOnForm()
            //     ->formatValue(fn($v) => $v
            //         ? '<img src="' . $v . '" width="80" height="80" style="object-fit:cover">'
            //         : '—'),

            // ImageField::new('coverImage', 'Pochette')->onlyOnDetail(),

            // Champs ArrayField avec template personnalisé
            ArrayField::new('albums', 'Albums')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/array_list.html.twig'),

            // Field::new('members', 'Membres')
            //     ->onlyOnDetail()
            //     ->setTemplatePath('admin/fields/array_list.html.twig'),

            Field::new('subGenres', 'Sous-genres')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/array_list.html.twig'),

        ];
    }



    public function fetchFromMusicBrainz(EntityManagerInterface $em): RedirectResponse
    {
        $artist = $this->getContext()->getEntity()->getInstance();
        $mbid = trim($artist->getMbid());

        if (!$mbid || !preg_match('/^[0-9a-fA-F-]{36}$/', $mbid)) {
            $this->addFlash('danger', 'MBID invalide');
            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'edit',
                'crudControllerFqcn' => self::class,
                'entityId' => $artist->getId(),
            ]));
        }

        try {
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query' => [
                    'inc' => 'releases+artist-rels+tags+genres+aliases+annotation',
                    'fmt' => 'json'
                ],
            ]);

            $data = $response->toArray();

            // Albums
            $albums = array_map(fn($r) => $r['title'] ?? '', $data['releases'] ?? []);
            $artist->setAlbums($albums);

            // Membres
            $members = [];
            foreach ($data['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $members[] = [
                        'name' => $rel['artist']['name'],
                        'instruments' => $rel['attributes'] ?? [], // peut être vide
                    ];
                }
            }
            $artist->setMembers($members);

            // Genres
            $mainGenre = $data['type'] ?? '';
            $subGenres = [];
            if (!empty($data['tags'])) {
                usort($data['tags'], fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                $mainGenre = $data['tags'][0]['name'] ?? $mainGenre;
                foreach ($data['tags'] as $tag) {
                    if (($tag['name'] ?? '') !== $mainGenre) {
                        $subGenres[] = $tag['name'];
                    }
                }
            }
            $artist->setMainGenre($mainGenre);
            $artist->setSubGenres($subGenres);

            // Autres champs
            $artist->setName($data['name'] ?? $artist->getName());
            $artist->setCountry($data['area']['name'] ?? $artist->getCountry());

            if (!empty($data['life-span']['begin'])) {
                $artist->setFoundedYear((int) substr($data['life-span']['begin'], 0, 4));
            }

            if (!empty($data['releases'][0]['id'])) {
                $releaseId = $data['releases'][0]['id'];
                $artist->setCoverImage("https://coverartarchive.org/release/{$releaseId}/front-500.jpg");
            }

            $em->flush();
            $this->addFlash('success', 'Données récupérées !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur MusicBrainz : ' . $e->getMessage());
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

        return $actions
             ->add(Crud::PAGE_EDIT, $fetch)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    
    }
}
