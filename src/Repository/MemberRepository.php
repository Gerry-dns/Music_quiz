<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * Récupère un membre avec tous ses instruments et memberships,
     * incluant les instruments joués dans chaque groupe
     */
    public function findFullMemberData(int $memberId): ?Member
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.memberInstruments', 'mi')->addSelect('mi')
            ->leftJoin('mi.instrument', 'i')->addSelect('i')
            ->leftJoin('m.artistMemberships', 'am')->addSelect('am')
            ->leftJoin('am.artist', 'a')->addSelect('a')
            ->leftJoin('am.memberInstruments', 'ami')->addSelect('ami')
            ->leftJoin('ami.instrument', 'iai')->addSelect('iai')
            ->where('m.id = :id')
            ->setParameter('id', $memberId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithInstrumentsAndMemberships(int $memberId): ?Member
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.memberInstruments', 'mi')->addSelect('mi')
            ->leftJoin('m.artistMemberships', 'am')->addSelect('am')
            ->where('m.id = :id')
            ->setParameter('id', $memberId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByInstrument(string $instrumentName): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.memberInstruments', 'mi')
            ->leftJoin('mi.instrument', 'i')
            ->where('i.name = :instrument')
            ->setParameter('instrument', $instrumentName)
            ->getQuery()
            ->getResult();
    }

    public function findByArtistMembership(int $artistId): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.artistMemberships', 'am')
            ->leftJoin('am.artist', 'a')
            ->where('a.id = :artistId')
            ->setParameter('artistId', $artistId)
            ->getQuery()
            ->getResult();
    }

    public function findByInstrumentAndArtist(string $instrumentName, int $artistId): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.memberInstruments', 'mi')
            ->leftJoin('mi.instrument', 'i')
            ->leftJoin('m.artistMemberships', 'am')
            ->leftJoin('am.artist', 'a')
            ->where('i.name = :instrument')
            ->andWhere('a.id = :artistId')
            ->setParameters([
                'instrument' => $instrumentName,
                'artistId' => $artistId,
            ])
            ->getQuery()
            ->getResult();
    }
}
