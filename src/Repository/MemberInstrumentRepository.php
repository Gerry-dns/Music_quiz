<?php

namespace App\Repository;

use App\Entity\MemberInstrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MemberInstrumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberInstrument::class);
    }

    /**
     * Retourne tous les membres jouant un instrument donné
     */
    public function findByInstrumentName(string $instrumentName): array
    {
        return $this->createQueryBuilder('mi')
            ->leftJoin('mi.member', 'm')->addSelect('m')
            ->leftJoin('mi.instrument', 'i')->addSelect('i')
            ->where('i.name = :instrument')
            ->setParameter('instrument', $instrumentName)
            ->getQuery()
            ->getResult();
    }
}
