<?php

// src/Repository/ArtistMemberInstrumentRepository.php
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
}
