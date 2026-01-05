<?php

namespace App\Repository;

use App\Entity\ArtistInstrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArtistInstrumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtistInstrument::class);
    }

    /**
     * Récupère tous les instruments d’un artiste avec le count
     */
    public function findInstrumentsByArtist(int $artistId): array
    {
        return $this->createQueryBuilder('ai')
            ->join('ai.instrument', 'i')
            ->addSelect('i')
            ->where('ai.artist = :artistId')
            ->setParameter('artistId', $artistId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un instrument précis pour un artiste
     */
    public function findOneByArtistAndInstrument(int $artistId, int $instrumentId): ?ArtistInstrument
    {
        return $this->createQueryBuilder('ai')
            ->where('ai.artist = :artistId')
            ->andWhere('ai.instrument = :instrumentId')
            ->setParameter('artistId', $artistId)
            ->setParameter('instrumentId', $instrumentId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
