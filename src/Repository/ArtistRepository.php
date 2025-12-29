<?php

namespace App\Repository;

use App\Entity\Artist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artist>
 */
class ArtistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    /**
     * Récupère un ou plusieurs artistes au hasard
     */
    public function findRandomArtists(int $limit = 1): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT id FROM artist';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        if (!$rows) return [];

        $allIds = array_column($rows, 'id');
        shuffle($allIds);
        $randomIds = array_slice($allIds, 0, $limit);

        return $this->createQueryBuilder('a')
            ->andWhere('a.id IN (:ids)')
            ->setParameter('ids', $randomIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un artiste par son MBID
     */
    public function findOneByMbid(string $mbid): ?Artist
    {
        return $this->findOneBy(['mbid' => $mbid]);
    }

    /**
     * Récupère un artiste avec toutes ses relations importantes
     */
    public function getArtistFullData(int $id): ?Artist
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.albums', 'al')->addSelect('al')
            ->leftJoin('a.artistMembers', 'am')->addSelect('am')
            ->leftJoin('a.artistSubGenres', 'asg')->addSelect('asg')
            ->leftJoin('a.mainGenre', 'g')->addSelect('g')
            ->leftJoin('a.country', 'c')->addSelect('c')
            ->leftJoin('a.beginArea', 'city')->addSelect('city')
            ->leftJoin('a.decades', 'd')->addSelect('d')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère le premier album d'un artiste
     */
    public function getFirstAlbum(Artist $artist): ?string
    {
        $albums = $artist->getAlbums()->toArray();
        return !empty($albums) ? $albums[0]->getTitle() : null;
    }

    /**
     * Récupère le dernier album d'un artiste
     */
    public function getLastAlbum(Artist $artist): ?string
    {
        $albums = $artist->getAlbums()->toArray();
        return !empty($albums) ? $albums[array_key_last($albums)]->getTitle() : null;
    }

    /**
     * Récupère des albums aléatoires pour générer des mauvaises réponses
     */
    public function getRandomAlbums(string $exclude, int $count = 3): array
    {
        $artists = $this->createQueryBuilder('a')
            ->leftJoin('a.albums', 'al')
            ->addSelect('al')
            ->getQuery()
            ->getResult();

        $allAlbums = [];
        foreach ($artists as $artist) {
            foreach ($artist->getAlbums() as $album) {
                $title = $album->getTitle();
                if ($title && $title !== $exclude) $allAlbums[] = $title;
            }
        }

        shuffle($allAlbums);
        return array_slice($allAlbums, 0, $count);
    }

    // Les autres méthodes de filtrage (findWithFilters, findByCountry...) doivent être adaptées de la même façon
    // pour utiliser les relations Albums et non plus JSON
}
