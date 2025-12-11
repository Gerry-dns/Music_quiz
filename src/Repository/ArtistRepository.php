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
     *
     * @param int $limit
     * @return Artist[]
     */
    public function findRandomArtists(int $limit = 1): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Récupérer tous les IDs des artistes
        $sql = 'SELECT id FROM artist';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        if (!$rows) {
            return [];
        }

        // Extraire les IDs
        $allIds = array_column($rows, 'id');

        // Mélanger les IDs et prendre le nombre demandé
        shuffle($allIds);
        $randomIds = array_slice($allIds, 0, $limit);

        // Récupérer les artistes correspondants
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
}
