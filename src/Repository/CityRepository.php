<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    // Exemple de méthode personnalisée
    public function findByCountryName(string $countryName): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.country', 'co')
            ->andWhere('co.name = :name')
            ->setParameter('name', $countryName)
            ->getQuery()
            ->getResult();
    }
}
