<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\ArtistMember;
use App\Entity\ArtistSubGenre;
use App\Entity\Genre;
use App\Entity\Country;
use App\Entity\City;
use App\Entity\Decade;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Album;
use App\Entity\ArtistInstrument;
use App\Entity\ArtistMemberInstrument;
use App\Entity\Instrument;
use App\Entity\Member;

class ArtistPopulatorService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function populateFromMusicBrainz(Artist $artist, array $artistData): void
    {
        // dd($artistData);
        $artist->setName($artistData['name']);
        $artist->setMbid($artistData['mbid'] ?? null);
        // Albums
        if (!empty($artistData['albums'])) {
            foreach ($artistData['albums'] as $albumData) {

                // Sécurité minimale
                if (!isset($albumData['id'], $albumData['title'])) {
                    continue;
                }

                // On ne prend que les albums officiels
                if (($albumData['status'] ?? '') !== 'Official') {
                    continue;
                }

                $album = new Album();
                $album->setTitle($albumData['title']);
                $album->setMbid($albumData['id']);

                // relation
                $artist->addAlbum($album);

                // persistance
                $this->em->persist($album);
            }
        }

        // Members → créer Member + ArtistMember + ArtistMemberInstruments
        if (!empty($artistData['members'])) {
            foreach ($artistData['members'] as $memberData) {

                // 1️⃣ Créer ou récupérer la personne globale
                // Récupérer ou créer le Member global
                $memberEntity = $this->em->getRepository(Member::class)->findOneBy(['name' => $memberData['name']]);
                if (!$memberEntity) {
                    $memberEntity = new Member();
                    $memberEntity->setName($memberData['name']);
                    $this->em->persist($memberEntity);
                }

                // Créer l'entrée ArtistMember pour ce membre dans cet artiste
                $artistMember = new ArtistMember();
                $artistMember->setArtist($artist);
                $artistMember->setMember($memberEntity); // <-- ici on passe l'objet Member
                $artistMember->setBegin($memberData['begin'] ?? null);
                $artistMember->setEnd($memberData['end'] ?? null);
                $artistMember->setIsOriginal(
                    in_array('original', array_map('strtolower', $memberData['instruments'] ?? []))
                );

                $artist->addArtistMember($artistMember);
                $this->em->persist($artistMember);

                // 3️⃣ Créer les instruments joués par ce membre dans ce groupe
                foreach ($memberData['instruments'] ?? [] as $instrName) {
                    if (strtolower($instrName) === 'original') continue;

                    // Cherche ou crée l'instrument
                    $instrumentEntity = $this->em->getRepository(Instrument::class)
                        ->findOneBy(['name' => $instrName]);
                    if (!$instrumentEntity) {
                        $instrumentEntity = new Instrument();
                        $instrumentEntity->setName($instrName);
                        $this->em->persist($instrumentEntity);
                    }

                    // Lie l'instrument au ArtistMember
                    $artistMemberInstrument = new ArtistMemberInstrument();
                    $artistMemberInstrument->setArtistMember($artistMember);
                    $artistMemberInstrument->setInstrument($instrumentEntity);

                    $artistMember->addMemberInstrument($artistMemberInstrument);
                    $this->em->persist($artistMemberInstrument);
                }
            }
        }



        // Main genre → Genre
        if (!empty($artistData['mainGenre'])) {
            $genre = $this->em->getRepository(Genre::class)->findOneBy(['name' => $artistData['mainGenre']]);
            if (!$genre) {
                $genre = new Genre();
                $genre->setName($artistData['mainGenre']);
                $this->em->persist($genre);
            }
            $artist->setMainGenre($genre);
        }


        // Sub-genres → ArtistSubGenre
        if (!empty($artistData['subGenres'])) {
            foreach ($artistData['subGenres'] as $sub) {
                $subGenre = new ArtistSubGenre();
                $subGenre->setArtist($artist);

                // Chercher ou créer le Genre correspondant
                $genre = $this->em->getRepository(Genre::class)->findOneBy(['name' => $sub['name']]);
                if (!$genre) {
                    $genre = new Genre();
                    $genre->setName($sub['name']);
                    $this->em->persist($genre);
                }

                $subGenre->setGenre($genre);
                $subGenre->setCount($sub['count'] ?? 0);

                $artist->addArtistSubGenre($subGenre);
                $this->em->persist($subGenre);
            }
        }


        // Country
        if (!empty($artistData['country'])) {
            $country = $this->em->getRepository(Country::class)->findOneBy(['name' => $artistData['country']]);
            if (!$country) {
                $country = new Country();
                $country->setName($artistData['country']);
                $this->em->persist($country);
            }
            $artist->setCountry($country);
        }

        // BeginArea → City
        if (!empty($artistData['beginArea'])) {
            $city = $this->em->getRepository(City::class)->findOneBy(['name' => $artistData['beginArea']]);
            if (!$city) {
                $city = new City();
                $city->setName($artistData['beginArea']);
                $this->em->persist($city);
            }
            $artist->setBeginArea($city);
        }

        // Decades → calcul à partir du life-span
        if (!empty($artistData['lifeSpan']['begin'])) {
            $year = (int)substr($artistData['lifeSpan']['begin'], 0, 4);
            $decadeName = floor($year / 10) * 10 . 's';
            $decade = $this->em->getRepository(Decade::class)->findOneBy(['name' => $decadeName]);
            if (!$decade) {
                $decade = new Decade();
                $decade->setName($decadeName);
                $this->em->persist($decade);
            }
            $artist->addDecade($decade);
        }

        // URLs
        if (!empty($artistData['urls'])) {
            $artist->setUrls($artistData['urls']);
        }

        // Flush à la fin si nécessaire
        $this->em->persist($artist);
    }
}
