<?php

namespace App\Repository;

use App\Entity\ArtistMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArtistMember>
 */


// Tous les membres d’un artiste/groupe.

// Tous les instruments joués par un membre dans un groupe.

// Les membres originaux d’un groupe.

// Récupérer un membre précis dans un groupe.

// Instruments principaux d’un membre dans un groupe.

// Membres jouant un instrument spécifique dans un groupe.


class ArtistMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtistMember::class);
    }

    /**
     * Récupère tous les membres d’un artiste/groupe
     */
    public function findMembersByArtist(int $artistId): array
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.member', 'm')
            ->addSelect('m')
            ->where('am.artist = :artistId')
            ->setParameter('artistId', $artistId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les instruments joués par un membre dans un artiste/groupe
     */
    public function findInstrumentsByMemberAndArtist(int $memberId, int $artistId): array
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.memberInstruments', 'ami')
            ->addSelect('ami')
            ->leftJoin('ami.instrument', 'i')
            ->addSelect('i')
            ->where('am.artist = :artistId')
            ->andWhere('am.member = :memberId')
            ->setParameters([
                'artistId' => $artistId,
                'memberId' => $memberId
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les membres originaux d’un artiste/groupe
     */
    public function findOriginalMembers(int $artistId): array
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.member', 'm')
            ->addSelect('m')
            ->where('am.artist = :artistId')
            ->andWhere('am.isOriginal = true')
            ->setParameter('artistId', $artistId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Cherche un membre spécifique dans un artiste/groupe
     */
    public function findMemberInArtist(int $memberId, int $artistId): ?ArtistMember
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.member', 'm')
            ->addSelect('m')
            ->where('am.artist = :artistId')
            ->andWhere('am.member = :memberId')
            ->setParameters([
                'artistId' => $artistId,
                'memberId' => $memberId
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère tous les instruments principaux joués par un membre dans un groupe
     */
    public function findPrimaryInstrumentsByMemberAndArtist(int $memberId, int $artistId): array
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.memberInstruments', 'ami')
            ->addSelect('ami')
            ->leftJoin('ami.instrument', 'i')
            ->addSelect('i')
            ->where('am.artist = :artistId')
            ->andWhere('am.member = :memberId')
            ->andWhere('ami.primary = true')
            ->setParameters([
                'artistId' => $artistId,
                'memberId' => $memberId
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les membres qui jouent un instrument spécifique dans un artiste/groupe
     */
    public function findMembersByInstrumentAndArtist(string $instrumentName, int $artistId): array
    {
        return $this->createQueryBuilder('am')
            ->leftJoin('am.memberInstruments', 'ami')
            ->addSelect('ami')
            ->leftJoin('ami.instrument', 'i')
            ->addSelect('i')
            ->leftJoin('am.member', 'm')
            ->addSelect('m')
            ->where('am.artist = :artistId')
            ->andWhere('i.name = :instrumentName')
            ->setParameters([
                'artistId' => $artistId,
                'instrumentName' => $instrumentName
            ])
            ->getQuery()
            ->getResult();
    }
}
