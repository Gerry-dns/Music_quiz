<?php

// src/Service/ArtistPopulatorService.php
namespace App\Service;

use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

class ArtistPopulatorService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function populateFromMusicBrainz(Artist $artist, array $data): void
    {
        // Nom
        $artist->setName($data['name'] ?? $artist->getName());

        // --- Main genre ---
        if (!empty($data['mainGenre'])) {
            $slug = strtolower(trim($data['mainGenre']));
            $genre = $this->em->getRepository(Genre::class)->findOneBy(['slug' => $slug]);
            if (!$genre) {
                $genre = new Genre();
                $genre->setName($data['mainGenre']);
                $genre->setSlug($slug);
                $this->em->persist($genre);
            }
            $artist->setMainGenre($genre);
        } else {
            $artist->setMainGenre(null);
        }

        // Sub-genres (on garde les strings ici)
        $artist->setSubGenres($data['subGenres'] ?? []);

        // Albums
        $artist->setAlbums($data['albums'] ?? []);

        // Members
        $artist->setMembers($data['members'] ?? []);

        // Life span
        $artist->setLifeSpan($data['lifeSpan'] ?? []);

        // Begin area
        $artist->setBeginArea($data['beginArea'] ?? null);

        // --- Country ---
        if (!empty($data['country'])) {
            $country = $this->em->getRepository(Country::class)->findOneBy(['name' => $data['country']]);
            if (!$country) {
                $country = new Country();
                $country->setName($data['country']);
                $this->em->persist($country);
            }
            $artist->setCountry($country);
        } else {
            $artist->setCountry(null);
        }

      
    }
}
