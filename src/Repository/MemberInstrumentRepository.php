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

    // Tu peux ajouter ici des méthodes personnalisées si nécessaire
}
