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

        // Sub-genres (on garde les strings)
        $artist->setSubGenres($data['subGenres'] ?? []);

        // Albums
        $albums = $data['albums'] ?? [];
        $existing = $artist->getAlbums() ?? [];
        $existing = array_unique(array_map('trim', $existing));

        foreach ($albums as $album) {
            if (is_array($album)) {
                $title = trim($album['title'] ?? '');
                $date = $album['firstReleaseDate'] ?? null;
            } else {
                // Cas où l’album est juste une string
                $title = trim($album);
                $date = null;
            }

            // Vérifier si cet album existe déjà
            $exists = false;
            foreach ($existing as $ex) {
                $exTitle = is_array($ex) ? ($ex['title'] ?? '') : (string)$ex;
                $exDate = is_array($ex) ? ($ex['firstReleaseDate'] ?? null) : null;

                if ($exTitle === $title && $exDate === $date) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $existing[] = ['title' => $title, 'firstReleaseDate' => $date];
            }
        }

        // Tri par date
        usort($existing, fn($a, $b) => strcmp($a['firstReleaseDate'] ?? '', $b['firstReleaseDate'] ?? ''));

        $artist->setAlbums($existing);


        // Trier par date
        usort($existing, fn($a, $b) => strcmp($a['firstReleaseDate'] ?? '', $b['firstReleaseDate'] ?? ''));

        $artist->setAlbums($existing);

        $existingMembers = $artist->getMembers() ?? [];
        foreach ($data['members'] ?? [] as $member) {
            $name = $member['name'];
            $found = false;
            foreach ($existingMembers as &$ex) {
                if ($ex['name'] === $name) {
                    // Fusion instruments
                    $ex['instruments'] = array_unique(array_merge(
                        $ex['instruments'] ?? [],
                        $member['instruments'] ?? []
                    ));
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $existingMembers[] = $member;
            }
        }
        $artist->setMembers($existingMembers);


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
    public function populateTracksFromReleases(Artist $artist, MusicBrainzService $mbService): void
    {
        $releases = $artist->getAlbums() ?? [];
        $tracks = [];

        foreach ($releases as $release) {
            $mbid = $release['id'] ?? null;
            if ($mbid) {
                $tracks = array_merge($tracks, $mbService->getTracksFromRelease($mbid));
            }
        }

        $artist->setTracks(array_unique($tracks));
    }
}
