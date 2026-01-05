<?php

namespace App\Repository;

use App\Entity\ArtistMemberInstrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArtistMemberInstrumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtistMemberInstrument::class);
    }

    /**
     * Retourne tous les instruments joués par un membre dans un groupe spécifique
     */
    public function findByArtistMember(int $artistMemberId): array
    {
        return $this->createQueryBuilder('ami')
            ->leftJoin('ami.instrument', 'i')->addSelect('i')
            ->where('ami.artistMember = :amId')
            ->setParameter('amId', $artistMemberId)
            ->getQuery()
            ->getResult();
    }
}
