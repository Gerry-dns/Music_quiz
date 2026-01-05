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
use App\Entity\MemberInstrument;

class ArtistPopulatorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private WikipediaByNameService $wikipediaByNameService,
        private WikidataService $wikidataService
    ) {}

    public function populateFromMusicBrainz(Artist $artist, array $artistData): void
    {


        $artist->setName($artistData['name']);
        $artist->setMbid($artistData['mbid'] ?? null);


        // Albums
        // Albums
        if (!empty($artistData['albums'])) {
            foreach ($artistData['albums'] as $albumData) {

                if (!isset($albumData['id'], $albumData['title'])) {
                    continue;
                }

                // éviter les doublons (MBID + artiste)
                $album = $this->em->getRepository(Album::class)->findOneBy([
                    'mbid' => $albumData['id'],
                    'artist' => $artist,
                ]);

                if (!$album) {
                    $album = new Album();
                    $album->setMbid($albumData['id']);
                    $album->setArtist($artist);
                    $this->em->persist($album);
                }

                // mise à jour (idempotente)
                $album->setTitle($albumData['title']);

                if (!empty($albumData['firstReleaseDate'])) {
                    $album->setFirstReleaseDate(
                        new \DateTime($albumData['firstReleaseDate'])
                    );
                }
            }
        }

        // --- Enrichissement Wikipedia ---
        $wikiData = $this->wikipediaByNameService->fetchSummaryByName($artist->getName());
        if ($wikiData) {
            $artist->setSummary($wikiData['summary'] ?? null);
            $artist->setCoverImage($wikiData['image'] ?? null);

            $urls = $artist->getUrls() ?? [];
            $urls['wikipedia'] = $wikiData['url'] ?? null;
            $artist->setUrls($urls);
        }

        // --- Enrichissement Wikidata (dd pour debug) ---
        // --- Enrichissement Wikidata ---
        $wikidataUrl = $artist->getUrls()['wikidata'] ?? null;
        if ($wikidataUrl) {
            $wikidataId = basename($wikidataUrl);

            // 1️⃣ Récupère les membres avec instruments
            $members = $this->wikidataService->fetchMembers($wikidataId);

            // 2️⃣ Récupère les sous-genres
            $subGenres = $this->wikidataService->fetchSubGenres($wikidataId);

            // 3️⃣ Parcours des membres Wikidata
            foreach ($members as $memberName => $instruments) {
                // Cherche ou crée le Member global
                $memberEntity = $this->em->getRepository(Member::class)->findOneBy(['name' => $memberName]);
                if (!$memberEntity) {
                    $memberEntity = new Member();
                    $memberEntity->setName($memberName);
                    $this->em->persist($memberEntity);
                }

                // Cherche ou crée ArtistMember
                $artistMember = $this->em->getRepository(ArtistMember::class)
                    ->findOneBy(['artist' => $artist, 'member' => $memberEntity]);
                if (!$artistMember) {
                    $artistMember = new ArtistMember();
                    $artistMember->setArtist($artist);
                    $artistMember->setMember($memberEntity);
                    $artist->addArtistMember($artistMember);
                    $this->em->persist($artistMember);
                }

                // Instruments du membre
                foreach ($instruments as $instrName) {
                    $instrNameLower = strtolower($instrName);
                    // Cherche ou crée Instrument
                    $instrumentEntity = $this->em->getRepository(Instrument::class)->findOneBy(['name' => $instrName]);
                    if (!$instrumentEntity) {
                        $instrumentEntity = new Instrument();
                        $instrumentEntity->setName($instrName);
                        $this->em->persist($instrumentEntity);
                    }

                    // ArtistMemberInstrument
                    $existingAMI = $this->em->getRepository(ArtistMemberInstrument::class)
                        ->findOneBy(['artistMember' => $artistMember, 'instrument' => $instrumentEntity]);
                    if (!$existingAMI) {
                        $artistMemberInstrument = new ArtistMemberInstrument();
                        $artistMemberInstrument->setArtistMember($artistMember);
                        $artistMemberInstrument->setInstrument($instrumentEntity);
                        $artistMember->addMemberInstrument($artistMemberInstrument);
                        $this->em->persist($artistMemberInstrument);

                        dump($artistMemberInstrument);
                    }

                    // ArtistInstrument
                    $existingAI = $this->em->getRepository(ArtistInstrument::class)
                        ->findOneBy(['artist' => $artist, 'instrument' => $instrumentEntity]);
                    if (!$existingAI) {
                        $artistInstrument = new ArtistInstrument();
                        $artistInstrument->setArtist($artist);
                        $artistInstrument->setInstrument($instrumentEntity);
                        $artist->addArtistInstrument($artistInstrument);
                        $this->em->persist($artistInstrument);
                    }

                    // MemberInstrument
                    $existingMI = $this->em->getRepository(MemberInstrument::class)
                        ->findOneBy(['member' => $memberEntity, 'instrument' => $instrumentEntity]);
                    if (!$existingMI) {
                        $memberInstrument = new MemberInstrument();
                        $memberInstrument->setMember($memberEntity);
                        $memberInstrument->setInstrument($instrumentEntity);
                        $memberEntity->addMemberInstrument($memberInstrument);
                        $this->em->persist($memberInstrument);
                    }
                }
            }

            // Parcours des sous-genres Wikidata
            foreach ($subGenres as $subGenreName) {
                $genre = $this->em->getRepository(Genre::class)->findOneBy(['name' => $subGenreName]);
                if (!$genre) {
                    $genre = new Genre();
                    $genre->setName($subGenreName);
                    $this->em->persist($genre);
                }

                $artistSubGenre = $this->em->getRepository(ArtistSubGenre::class)
                    ->findOneBy(['artist' => $artist, 'genre' => $genre]);
                if (!$artistSubGenre) {
                    $artistSubGenre = new ArtistSubGenre();
                    $artistSubGenre->setArtist($artist);
                    $artistSubGenre->setGenre($genre);
                    $artistSubGenre->setCount(0);
                    $artist->addArtistSubGenre($artistSubGenre);
                    $this->em->persist($artistSubGenre);
                }
            }
        }

        // Members → créer Member + ArtistMember + ArtistMemberInstruments + ArtistInstrument + MemberInstrument
        if (!empty($artistData['members'])) {
            foreach ($artistData['members'] as $memberData) {

                // 1️⃣ Créer ou récupérer la personne globale
                $memberEntity = $this->em->getRepository(Member::class)->findOneBy(['name' => $memberData['name']]);
                if (!$memberEntity) {
                    $memberEntity = new Member();
                    $memberEntity->setName($memberData['name']);
                    $this->em->persist($memberEntity);
                }

                // 2️⃣ Créer ou récupérer l'entrée ArtistMember
                $artistMember = $this->em->getRepository(ArtistMember::class)
                    ->findOneBy(['artist' => $artist, 'member' => $memberEntity]);

                if (!$artistMember) {
                    $artistMember = new ArtistMember();
                    $artistMember->setArtist($artist);
                    $artistMember->setMember($memberEntity);

                    $beginDate = !empty($memberData['begin']) ? new \DateTime($memberData['begin']) : null;
                    $endDate   = !empty($memberData['end']) ? new \DateTime($memberData['end']) : null;

                    $artistMember->setBegin($beginDate);
                    $artistMember->setEnd($endDate);

                    // Conserver l'info “original”
                    $artistMember->setIsOriginal(
                        in_array('original', array_map('strtolower', $memberData['instruments'] ?? []))
                    );

                    $artist->addArtistMember($artistMember);
                    $this->em->persist($artistMember);
                }

                // --- Parcourir les instruments joués par ce membre ---
                foreach ($memberData['instruments'] ?? [] as $instrName) {
                    $instrNameClean = strtolower(trim($instrName));

                    // Si c’est “original”, c’est pour le flag isOriginal, on ne crée pas de lien d’instrument
                    if ($instrNameClean === 'original') continue;

                    // Cherche ou crée l'instrument avec nom normalisé
                    $instrumentEntity = $this->em->getRepository(Instrument::class)
                        ->findOneBy(['name' => $instrNameClean]);

                    if (!$instrumentEntity) {
                        $instrumentEntity = new Instrument();
                        $instrumentEntity->setName($instrNameClean);
                        $this->em->persist($instrumentEntity);

                        // flush immédiat pour éviter doublons internes Doctrine
                        $this->em->flush();
                    }

                    // --- ArtistMemberInstrument ---
                    $existingAMI = $this->em->getRepository(ArtistMemberInstrument::class)
                        ->findOneBy(['artistMember' => $artistMember, 'instrument' => $instrumentEntity]);

                    if (!$existingAMI) {
                        $artistMemberInstrument = new ArtistMemberInstrument();
                        $artistMemberInstrument->setArtistMember($artistMember);
                        $artistMemberInstrument->setInstrument($instrumentEntity);
                        $artistMember->addMemberInstrument($artistMemberInstrument);
                        $this->em->persist($artistMemberInstrument);
                    }

                    // --- ArtistInstrument ---
                    $existingAI = $this->em->getRepository(ArtistInstrument::class)
                        ->findOneBy(['artist' => $artist, 'instrument' => $instrumentEntity]);

                    if (!$existingAI) {
                        $artistInstrument = new ArtistInstrument();
                        $artistInstrument->setArtist($artist);
                        $artistInstrument->setInstrument($instrumentEntity);
                        $artist->addArtistInstrument($artistInstrument);
                        $this->em->persist($artistInstrument);
                    }

                    // --- MemberInstrument ---
                    $existingMI = $this->em->getRepository(MemberInstrument::class)
                        ->findOneBy(['member' => $memberEntity, 'instrument' => $instrumentEntity]);

                    if (!$existingMI) {
                        $memberInstrument = new MemberInstrument();
                        $memberInstrument->setMember($memberEntity);
                        $memberInstrument->setInstrument($instrumentEntity);
                        $memberEntity->addMemberInstrument($memberInstrument);
                        $this->em->persist($memberInstrument);
                    }
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

                // 1️⃣ Chercher ou créer le Genre
                $genre = $this->em->getRepository(Genre::class)
                    ->findOneBy(['name' => $sub['name']]);

                if (!$genre) {
                    $genre = new Genre();
                    $genre->setName($sub['name']);
                    $this->em->persist($genre);
                }

                // 2️⃣ Vérifier si le lien Artist ↔ Genre existe déjà
                $artistSubGenre = $this->em->getRepository(ArtistSubGenre::class)
                    ->findOneBy([
                        'artist' => $artist,
                        'genre'  => $genre
                    ]);

                // 3️⃣ Créer uniquement s’il n’existe pas
                if (!$artistSubGenre) {
                    $artistSubGenre = new ArtistSubGenre();
                    $artistSubGenre->setArtist($artist);
                    $artistSubGenre->setGenre($genre);
                    $artistSubGenre->setCount($sub['count'] ?? 0);

                    $artist->addArtistSubGenre($artistSubGenre);
                    $this->em->persist($artistSubGenre);
                }
            }
        }

        // Country
        $country = null;
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
                $city->setCountry($country);
                $this->em->persist($city);
            }
            $artist->setBeginArea($city);
        }

        // Dates et statut actif
        $artist->setBeginDate(!empty($artistData['lifeSpan']['begin']) ? new \DateTime($artistData['lifeSpan']['begin']) : null);
        $artist->setEndDate(!empty($artistData['lifeSpan']['end']) ? new \DateTime($artistData['lifeSpan']['end']) : null);
        $artist->setIsActive(empty($artistData['lifeSpan']['ended']));

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
        $this->em->persist($artist);
        $this->em->flush();
    }
}
