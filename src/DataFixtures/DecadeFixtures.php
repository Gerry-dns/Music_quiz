<?php

namespace App\DataFixtures;

use App\Entity\Decade;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DecadeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $decades = [
            ['name' => 'Années 60', 'startYear' => 1960, 'endYear' => 1969],
            ['name' => 'Années 70', 'startYear' => 1970, 'endYear' => 1979],
            ['name' => 'Années 80', 'startYear' => 1980, 'endYear' => 1989],
            ['name' => 'Années 90', 'startYear' => 1990, 'endYear' => 1999],
            ['name' => 'Années 2000', 'startYear' => 2000, 'endYear' => 2009],
            ['name' => 'Années 2010', 'startYear' => 2010, 'endYear' => 2019],
            ['name' => 'Années 2020', 'startYear' => 2020, 'endYear' => 2029],
        ];

        foreach ($decades as $data) {
            $decade = new Decade();
            $decade->setName($data['name'])
                   ->setStartYear($data['startYear'])
                   ->setEndYear($data['endYear']);

            $manager->persist($decade);
        }

        $manager->flush();
    }
}
