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

    /**
     * Récupère les artistes par pays
     */
    public function findByCountry(string $countryCode): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.country', 'c')
            ->andWhere('c.code = :code')
            ->setParameter('code', $countryCode)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Récupère les artistes par ville de formation
     */
    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.beginArea = :city')
            ->setParameter('city', $city)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Récupère les artistes par décennie
     */
    public function findByDecade(int $decade): array
    {
        $start = new \DateTime($decade . '-01-01');
        $end   = new \DateTime(($decade + 9) . '-12-31');

        return $this->createQueryBuilder('a')
            ->andWhere('a.beginDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('a.beginDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les artistes avec filtres combinés
     */
    public function findWithFilters(
        ?string $country = null,
        ?string $city = null,
        ?int $decade = null
    ): array {
        $qb = $this->createQueryBuilder('a');

        if ($country) {
            $qb->join('a.country', 'c')
                ->andWhere('c.code = :country')
                ->setParameter('country', $country);
        }

        if ($city) {
            $qb->andWhere('a.beginArea = :city')
                ->setParameter('city', $city);
        }

        if ($decade) {
            $start = new \DateTime($decade . '-01-01');
            $end   = new \DateTime(($decade + 9) . '-12-31');

            $qb->andWhere('a.beginDate BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
