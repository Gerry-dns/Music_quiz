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
        //    dd($data);

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

        // Sub-genres
        $artist->setSubGenres($data['subGenres'] ?? []);

        // --- Albums uniques à partir des release-groups ---
        $albums = [];
        $seen = [];

        foreach ($data['albums'] ?? [] as $album) {


            $title = $album['title'] ?? null;
            if (!$title) continue;

            $normalized = $this->normalizeAlbumName($title);
            if (in_array($normalized, $seen)) continue;

            $seen[] = $normalized;
            $albums[] = [
                'title' => $title,
                'firstReleaseDate' => $album['first-release-date'] ?? null,
            ];
        }
        // dd($albums);
        $artist->setAlbums($albums);

        // --- Releases complètes ---
        $releases = [];
        foreach ($data['releases'] ?? [] as $release) {
            $releases[] = [
                'title' => $release['title'] ?? null,
                'date' => $release['date'] ?? null,
                'format' => $release['release-group']['primary-type == "Album"'] ?? null,
            ];
        }

        $artist->setReleases($releases);


        // Membres
        $members = [];
        foreach ($data['members'] ?? [] as $member) {
            // Vérifie que c'est bien un artiste et que c'est un membre de band
            if (($member['type'] ?? '') === 'member of band' && isset($member['artist']['name'])) {
                $nameLower = strtolower($member['artist']['name']);

                // filtrer tributes / bootlegs
                if (strpos($nameLower, 'tribute') === false && strpos($nameLower, 'boot') === false && strpos($nameLower, 'cover') === false) {
                    $members[] = [
                        'name' => $member['artist']['name'],
                        'instruments' => $member['attributes'] ?? [],
                        'begin' => $member['begin'] ?? null,
                        'end' => $member['end'] ?? null,
                    ];
                }
            }
        }


        // dd($members);
        $artist->setMembers($members);



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

    private function normalizeAlbumName(string $name): string
    {
        // Supprime les caractères invalides UTF-8
        $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');

        $name = trim(mb_strtolower($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name); // <-- ajoute //IGNORE
        $name = preg_replace('/[^a-z0-9 ]/', '', $name);
        return $name;
    }
    public function getMembersWithInstruments(array $relations): array
    {
        $members = [];

        foreach ($relations as $relation) {
            if (($relation['type'] ?? '') === 'member of band' && isset($relation['artist']['name'])) {
                $members[] = [
                    'name' => $relation['artist']['name'],
                    'instruments' => is_array($relation['attribute-list'] ?? null) ? $relation['attribute-list'] : [],
                    'begin' => $relation['begin'] ?? null,
                    'end' => $relation['end'] ?? null
                ];
            }
        }

        return $members;
    }
}
