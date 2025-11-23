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
            TextField::new('name', 'Nom du groupe/artiste'),
            TextField::new('mbid', 'MusicBrainz ID (MBID)')
                ->setHelp('Ex : 0bfba3d5-0b7a-4d9e-9b3f-4e3c7a3d9b0f → Ramones'),
            TextField::new('genre'),
            TextField::new('country', 'Pays'),
            IntegerField::new('foundedYear', 'Année de création'),
            TextareaField::new('biography', 'Biographie')->hideOnIndex(),
            UrlField::new('coverImage', 'Pochette')
                ->hideOnForm()
                ->formatValue(fn ($v) => $v ? '<img src="'.$v.'" width="80" height="80" style="object-fit:cover">' : '—'),
            ImageField::new('coverImage', 'Aperçu pochette')
                ->onlyOnDetail()
                ->setBasePath('')
                ->setCssClass('img-fluid'),
            AssociationField::new('questions')->onlyOnDetail(),
        ];
    }

    public function configureActions(Actions $actions): Actions
{
    $fetch = Action::new('fetchFromMusicBrainz', 'Récupérer depuis MusicBrainz', 'fa-solid fa-sync-alt')
        ->linkToCrudAction('fetchFromMusicBrainz')
        ->addCssClass('btn btn-success btn-sm')
        ->displayAsButton()
        ->setHtmlAttributes(['title' => 'Remplir automatiquement']);

    return $actions
        ->add(Crud::PAGE_NEW, $fetch)
        ->add(Crud::PAGE_EDIT, $fetch);
}

    public function fetchFromMusicBrainz(EntityManagerInterface $em): RedirectResponse
    {
        $artist = $this->getContext()->getEntity()->getInstance();
        $mbid = $this->getMbidFromForm() ?? $artist->getMbid();

        if (!$mbid || strlen(trim($mbid)) !== 36) {
            $this->addFlash('danger', 'MBID invalide');
            return $this->redirectToRoute('admin');
        }

        try {
            $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                'query'   => ['inc' => 'releases', 'fmt' => 'json'],
            ]);

            $data = $response->toArray();

            $artist->setName($data['name'] ?? $artist->getName());
            $artist->setGenre($data['type'] ?? $artist->getGenre());
            $artist->setCountry($data['area']['name'] ?? $artist->getCountry());

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

        return $this->redirectToRoute('admin');
    }

    private function getMbidFromForm(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) return null;

        $ea = $request->request->all('ea') ?? [];
        $formData = $ea['newForm'] ?? $ea['editForm'] ?? [];
        return $formData['mbid'] ?? null;
    }
}