<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    // Méthode pour récupérer un genre aléatoire sauf celui à exclure
    public function getRandomGenreNames(string $exclude, int $limit = 3): array
    {
        $genres = $this->createQueryBuilder('g')
            ->where('g.name != :exclude')
            ->setParameter('exclude', $exclude)
            ->getQuery()
            ->getResult();

        if (empty($genres)) {
            return ['Genre Aléatoire'];
        }

        shuffle($genres);
        $selected = array_slice($genres, 0, $limit);

        return array_map(fn($g) => $g->getName(), $selected);
    }
}
