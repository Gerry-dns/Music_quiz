<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Decade;
use Doctrine\ORM\EntityManagerInterface;

class DecadeAssignerService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function assignArtistToDecades(Artist $artist): void
    {
        $lifeSpan = $artist->getLifeSpan();
        if (empty($lifeSpan['begin'])) {
            return; // pas de date de début
        }

        $beginYear = (int) $lifeSpan['begin'];
        $endYear   = isset($lifeSpan['end']) ? (int)$lifeSpan['end'] : (int)date('Y');

        $decades = $this->em->getRepository(Decade::class)->findAll();

        foreach ($decades as $decade) {
            if ($decade->getStartYear() <= $endYear && $decade->getEndYear() >= $beginYear) {
                $artist->addDecade($decade);
            }
        }

        $this->em->persist($artist);
        $this->em->flush();
    }
}
