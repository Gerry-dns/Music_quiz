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
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private HttpClientInterface $client,
        private RequestStack $requestStack
    ) {}

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            TextField::new('mbid', 'MBID'),

            TextField::new('genre', 'Genre'),
            TextField::new('country', 'Pays'),
            IntegerField::new('foundedYear', 'Année de création'),

            TextareaField::new('biography', 'Biographie')->hideOnIndex(),

            UrlField::new('coverImage', 'URL Pochette')
                ->hideOnForm()
                ->formatValue(fn($v) => $v
                    ? '<img src="'.$v.'" width="80" height="80" style="object-fit:cover">'
                    : '—'),

            ImageField::new('coverImage', 'Pochette')->onlyOnDetail(),

            AssociationField::new('questions')->onlyOnDetail(),

            ArrayField::new('albums', 'Albums')->onlyOnDetail(),
            ArrayField::new('members', 'Membres')->onlyOnDetail(),
        ];
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

   public function fetchFromMusicBrainz(EntityManagerInterface $em): RedirectResponse
{
    $artist = $this->getContext()->getEntity()->getInstance();
    $mbid = $this->getMbidFromForm() ?? $artist->getMbid();

    if (!$mbid || strlen(trim($mbid)) !== 36) {
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
                'inc' => 'releases+artist-rels',
                'fmt' => 'json'
            ],
        ]);

        $data = $response->toArray();

        $albums = [];
        foreach ($data['releases'] ?? [] as $release) {
            $albums[] = $release['title'];
        }

        $members = [];
        foreach ($data['relations'] ?? [] as $rel) {
            if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                $members[] = $rel['artist']['name'];
            }
        }

        $artist->setName($data['name'] ?? $artist->getName());
        $artist->setGenre($data['type'] ?? $artist->getGenre());
        $artist->setCountry($data['area']['name'] ?? $artist->getCountry());
        $artist->setAlbums($albums);
        $artist->setMembers($members);

        if (isset($data['life-span']['begin'])) {
            $artist->setFoundedYear((int) substr($data['life-span']['begin'], 0, 4));
        }

        if (!empty($data['releases'][0]['id'])) {
            $releaseId = $data['releases'][0]['id'];
            $artist->setCoverImage("https://coverartarchive.org/release/{$releaseId}/front-500.jpg");
        }

        $em->flush();
        $this->addFlash('success', 'Données récupérées !');

    } catch (\Exception $e) {
        $this->addFlash('danger', $e->getMessage());
    }

    return $this->redirect($this->generateUrl('admin', [
        'crudAction' => 'edit',
        'crudControllerFqcn' => self::class,
        'entityId' => $artist->getId(),
    ]));
}


    private function getMbidFromForm(): ?string
    {
        $req = $this->requestStack->getCurrentRequest();
        if (!$req) return null;

        $form = $req->request->all('ea')['editForm'] ?? $req->request->all('ea')['newForm'] ?? null;

        return $form['mbid'] ?? null;
    }
}
