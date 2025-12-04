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

    // On insère directement le nombre dans la requête
    $sql = 'SELECT id FROM artist ORDER BY RAND() LIMIT ' . (int)$limit;
    $stmt = $conn->prepare($sql);

    // DBAL 3 : executeQuery() retourne un Result
    $result = $stmt->executeQuery();

    // Récupérer tous les résultats en tableau associatif
    $rows = $result->fetchAllAssociative();

    // Transformer en objets Artist
    $artists = [];
    foreach ($rows as $row) {
        $artists[] = $this->find($row['id']);
    }

    return $artists;
}


    /**
     * Récupère un artiste par son MBID
     */
    public function findOneByMbid(string $mbid): ?Artist
    {
        return $this->findOneBy(['mbid' => $mbid]);
    }
}
