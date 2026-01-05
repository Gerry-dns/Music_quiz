<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Album;
use App\Entity\Country;
use App\Entity\Questions;
use App\Repository\GenreRepository;
use App\Repository\ArtistMemberRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuizGeneratorService
{
    private EntityManagerInterface $em;
    private GenreRepository $genreRepository;
    private ArtistMemberRepository $artistMemberRepository;
    private InstrumentHelper $instrumentHelper;

    public function __construct(
        EntityManagerInterface $em,
        GenreRepository $genreRepository,
        ArtistMemberRepository $artistMemberRepository,
        InstrumentHelper $instrumentHelper
    ) {
        $this->em = $em;
        $this->genreRepository = $genreRepository;
        $this->artistMemberRepository = $artistMemberRepository;
        $this->instrumentHelper = $instrumentHelper;
    }

    private function getRandomMemberNamesExcept(
        array $members,
        array $excludedNames,
        ?string $instrument = null
    ): array {
        $names = [];

        foreach ($members as $m) {
            $member = $m->getMember();
            if (!$member) {
                continue;
            }

            $name = $member->getName();

            // Exclusion des noms interdits
            if (in_array($name, $excludedNames, true)) {
                continue;
            }

            // Exclusion par instrument si demandé
            if ($instrument !== null && $m->hasInstrument($instrument)) {
                continue;
            }

            $names[] = $name;
        }

        $names = array_values(array_unique($names));
        shuffle($names);

        return array_slice($names, 0, 3);
    }



    /**
     * Génère toutes les questions possibles pour un artiste
     */
    public function generateQuestionsForArtist(Artist $artist): void
    {
        $questions = [];

        /* ==========================
         * 1️⃣ ALBUMS (premier / dernier)
         * ========================== */

        $albums = $artist->getAlbums();

        if ($albums->count() > 0) {
            $albumsSorted = $albums->toArray();

            usort(
                $albumsSorted,
                fn($a, $b) => $a->getReleaseDate() <=> $b->getReleaseDate()
            );

            $firstAlbum = $albumsSorted[0];
            $lastAlbum  = end($albumsSorted);

            if ($firstAlbum && $firstAlbum->getReleaseDate() instanceof \DateTimeInterface) {
                $this->addQuestionIfNotExists(
                    $questions,
                    "Quel est le premier album de « {$artist->getName()} » ?",
                    $firstAlbum->getTitle(),
                    $this->getRandomAlbumTitlesForArtist($artist, $firstAlbum->getTitle()),
                    'album',
                    2,
                    $artist
                );

                $this->addQuestionIfNotExists(
                    $questions,
                    "En quelle année est sorti l'album « {$firstAlbum->getTitle()} » ?",
                    (string) $firstAlbum->getReleaseDate()->format('Y'),
                    $this->getRandomYears((int) $firstAlbum->getReleaseDate()->format('Y')),
                    'year',
                    2,
                    $artist
                );
            }

            if (
                $lastAlbum &&
                $lastAlbum !== $firstAlbum &&
                $lastAlbum->getReleaseDate() instanceof \DateTimeInterface
            ) {
                $this->addQuestionIfNotExists(
                    $questions,
                    "Quel est le dernier album sorti par « {$artist->getName()} » ?",
                    $lastAlbum->getTitle(),
                    $this->getRandomAlbumTitlesForArtist($artist, $lastAlbum->getTitle()),
                    'album',
                    2,
                    $artist
                );



                $this->addQuestionIfNotExists(
                    $questions,
                    "En quelle année est sorti l'album « {$lastAlbum->getTitle()} » ?",
                    (string) $lastAlbum->getReleaseDate()->format('Y'),
                    $this->getRandomYears((int) $lastAlbum->getReleaseDate()->format('Y')),
                    'year',
                    2,
                    $artist
                );
            }
        }

        /* ==========================
         * 2️⃣ PAYS
         * ========================== */
        if ($artist->getCountry()) {
            $countryName = $artist->getCountry()->getName();
            $this->addQuestionIfNotExists(
                $questions,
                "De quel pays vient « {$artist->getName()} » ?",
                $countryName,
                $this->getRandomCountryNames($countryName),
                'country',
                1,
                $artist
            );
        }

        /* ==========================
         * 3️⃣ ANNÉE DE FORMATION
         * ========================== */
        $years = [];
        foreach ($artist->getArtistMembers() as $artistMember) {
            $begin = $artistMember->getBegin();
            if ($begin instanceof \DateTimeInterface) {
                $years[] = (int) $begin->format('Y');
            } elseif (is_string($begin) && preg_match('/^\d{4}/', $begin, $matches)) {
                $years[] = (int) $matches[0];
            }
        }

        if (!empty($years)) {
            $year = min($years);
            $this->addQuestionIfNotExists(
                $questions,
                "En quelle année s'est formé « {$artist->getName()} » ?",
                (string) $year,
                [
                    (string) ($year - 5),
                    (string) ($year + 5),
                    (string) ($year + 10),
                ],
                'year',
                2,
                $artist
            );
        }

        /* ==========================
         * 4️⃣ GENRE
         * ========================== */
        if ($artist->getMainGenre()) {
            $genreName = $artist->getMainGenre()->getName();
            $this->addQuestionIfNotExists(
                $questions,
                "Quel est le genre principal de « {$artist->getName()} » ?",
                $genreName,
                $this->getRandomGenres($genreName),
                'genre',
                1,
                $artist
            );
        }

        /* ==========================
         * 5️⃣ MEMBRE DU GROUPE
         * ========================== */
        $members = $this->artistMemberRepository->findMembersByArtist($artist->getId());
        if (!empty($members)) {
            $randomMember = $members[array_rand($members)];
            $memberName = $randomMember->getMember()?->getName();
            if ($memberName) {
                $this->addQuestionIfNotExists(
                    $questions,
                    "$memberName est membre de quel groupe ?",
                    $artist->getName(),
                    $this->getRandomArtistsNames($artist->getName()),
                    'member',
                    2,
                    $artist
                );
            }
        }

        /* ==========================
         * 6️⃣ VILLE DE FORMATION
         * ========================== */
        if ($artist->getBeginArea()) {
            $cityName = $artist->getBeginArea()->getName();
            $this->addQuestionIfNotExists(
                $questions,
                "Dans quelle ville s'est formé « {$artist->getName()} » ?",
                $cityName,
                $this->getRandomCityNames($cityName),
                'city',
                1,
                $artist
            );
        }


        /* ==========================
 * 7️⃣ MEMBRE / INSTRUMENT
 * ========================== */
        $members = $this->artistMemberRepository->findMembersByArtist($artist->getId());
        $handledReplacements = [];

        foreach ($members as $member) {
            $memberName = $member->getMember()?->getName();
            $instrumentNames = array_map(fn($mi) => $mi->getInstrument()?->getName(), $member->getMemberInstruments()->toArray());

            // 1️⃣ Question simple : "Untel est le guitariste de tel groupe"
            // Supposons que $instrumentNames contient ['lead vocals', 'guitar', ...]
            foreach ($instrumentNames as $instr) {
                $instrFrench = $this->instrumentHelper->toFrench($instr);
                $played = $member->hasInstrument($instr);
                $correctAnswer = $played ? 'Oui' : 'Non';
                $wrongAnswer = $played ? 'Non' : 'Oui';

                $this->addQuestionIfNotExists(
                    $questions,
                    "$memberName est-il $instrFrench du groupe « {$artist->getName()} » ?",
                    $correctAnswer,
                    [$wrongAnswer], // juste l'autre réponse
                    'member_instrument',
                    1,
                    $artist
                );
            }


            // 2️⃣ Question : "Untel est-il membre original ?"
            $isOriginal = $member->getIsOriginal();
            $correctAnswer = $isOriginal ? 'Oui' : 'Non';
            $wrongAnswer = $isOriginal ? 'Non' : 'Oui';

            $this->addQuestionIfNotExists(
                $questions,
                "$memberName est-il membre original de « {$artist->getName()} » ?",
                $correctAnswer,
                [$wrongAnswer],
                'original_member',
                1,
                $artist
            );

            // 3️⃣ Question : "Qui était le guitariste / bassiste du groupe ?"
            foreach ($instrumentNames as $instr) {
                $instrFrench = $this->instrumentHelper->toFrench($instr);
                $this->addQuestionIfNotExists(
                    $questions,
                    "Qui était $instrFrench du groupe « {$artist->getName()} » ?",
                    $memberName,
                    $this->getRandomMemberNamesExcept($members, [$memberName], $instr), // <- exclut les autres qui jouent le même instrument
                    'who_instrument',
                    2,
                    $artist
                );
            }
            // 4️⃣ Question : "Qui a été le guitariste de tel groupe sur cette période ?"
            $begin = $member->getBegin()?->format('Y-m-d') ?? null;
            $end = $member->getEnd()?->format('Y-m-d') ?? null;
            if ($begin && $end) {
                $this->addQuestionIfNotExists(
                    $questions,
                    "Qui a été $instrFrench du groupe « {$artist->getName()} » entre $begin et $end ?",
                    $memberName,
                    $this->getRandomMemberNamesExcept($members, [$memberName]),
                    'who_instrument_period',
                    3,
                    $artist
                );
            }

            $played = $member->hasInstrument('guitar'); // booléen

            $this->addQuestionIfNotExists(
                $questions,
                "$memberName a-t-il été guitariste du groupe « {$artist->getName()} » ?",
                $played ? 'Oui' : 'Non',
                [$played ? 'Non' : 'Oui', '', ''], // on met juste l’autre réponse, les deux autres restent vides
                'played_instrument',
                2,
                $artist
            );

            /* ==========================
 * 8️⃣ QUI A REMPLACÉ QUI
 * ========================== */
            foreach ($members as $member) {
                $memberName = $member->getMember()?->getName();
                if (!$memberName) {
                    continue;
                }

                $memberEnd = $member->getEnd();
                if (!$memberEnd instanceof \DateTimeInterface) {
                    continue;
                }

                $closestReplacement = null;
                $closestDateDiff = null;

                foreach ($members as $potentialReplacement) {
                    if ($potentialReplacement === $member) {
                        continue;
                    }

                    $replacementName = $potentialReplacement->getMember()?->getName();
                    if (!$replacementName) {
                        continue;
                    }

                    $replacementBegin = $potentialReplacement->getBegin();
                    if (!$replacementBegin instanceof \DateTimeInterface) {
                        continue;
                    }

                    $diff = $replacementBegin->getTimestamp() - $memberEnd->getTimestamp();

                    if ($diff >= 0 && ($closestDateDiff === null || $diff < $closestDateDiff)) {
                        $closestReplacement = $potentialReplacement;
                        $closestDateDiff = $diff;
                    }
                }

                if ($closestReplacement) {
                    $replacementName = $closestReplacement->getMember()?->getName();
                    if (!$replacementName) {
                        continue;
                    }

                    // 🔒 GARDE-FOU ANTI DOUBLON
                    $key = $artist->getId() . '|' . $memberName . '|' . $replacementName;
                    if (isset($handledReplacements[$key])) {
                        continue;
                    }
                    $handledReplacements[$key] = true;

                    $this->addQuestionIfNotExists(
                        $questions,
                        "Qui a remplacé $memberName dans le groupe « {$artist->getName()} » ?",
                        $replacementName,
                        $this->getRandomMemberNamesExcept(
                            $members,
                            [$memberName, $replacementName] // 👈 exclusion correcte
                        ),
                        'replaced_member',
                        3,
                        $artist
                    );
                }
            }
            /* ==========================
 * DURÉE DU GROUPE + ACTIF ?
 * ========================== */
        }

        // Année de début
        if ($artist->getBeginDate() !== null) {
            $beginYear = (int) $artist->getBeginDate()->format('Y');
            $this->addQuestionIfNotExists(
                $questions,
                "En quelle année le groupe « {$artist->getName()} » a-t-il débuté ?",
                (string) $beginYear,
                [
                    (string) ($beginYear - 1),
                    (string) ($beginYear + 1),
                    (string) ($beginYear + 2),
                ],
                'group_duration',
                2,
                $artist
            );
        }

        // Année de fin
        if ($artist->getEndDate() !== null) {
            $endYear = (int) $artist->getEndDate()->format('Y');
            $this->addQuestionIfNotExists(
                $questions,
                "En quelle année le groupe « {$artist->getName()} » s'est-il séparé ?",
                (string) $endYear,
                [
                    (string) ($endYear - 1),
                    (string) ($endYear + 1),
                    (string) ($endYear + 2),
                ],
                'group_duration',
                2,
                $artist
            );
        }

        // Toujours actif ?
        $isActive = $artist->getIsActive();
        $this->addQuestionIfNotExists(
            $questions,
            "Le groupe « {$artist->getName()} » est-il toujours actif ?",
            $isActive ? 'Oui' : 'Non',
            [$isActive ? 'Non' : 'Oui'],
            'group_duration',
            1,
            $artist
        );
         $this->addAlbumArtistQuestion($questions, $artist); 

        $this->addWhichAlbumIsArtistQuestion($questions, $artist);

        /* ==========================
         * PERSISTENCE
         * ========================== */
        foreach ($questions as $question) {
            $this->em->persist($question);
        }
        $this->em->flush();
    }

    /* ==========================
     * MÉTHODES UTILITAIRES
     * ========================== */

    private function addQuestionIfNotExists(array &$questions, string $text, string $correct, array $wrongs, string $category, int $difficulty, Artist $artist): void
    {
        // Vérifie si la question existe déjà
        $exists = $this->em->getRepository(Questions::class)->findOneBy([
            'artist' => $artist,
            'text' => $text,
            'correctAnswer' => $correct
        ]);

        if (!$exists) {
            $questions[] = $this->createQuestion($text, $correct, $wrongs, $category, $difficulty, $artist);
        }
    }




    private function createQuestion(string $text, string $correct, array $wrongs, string $category, int $difficulty, Artist $artist): Questions
    {
        $q = new Questions();
        $q->setText($text);
        $q->setCorrectAnswer($correct);
        $q->setWrongAnswer1($wrongs[0] ?? '');
        $q->setWrongAnswer2($wrongs[1] ?? '');
        $q->setWrongAnswer3($wrongs[2] ?? '');
        $q->setCategory($category);
        $q->setDifficulty($difficulty);
        $q->setArtist($artist);
        $q->setPlayedCount(0);
        $q->setCorrectCount(0);

        return $q;
    }

    private function getRandomArtistsNames(string $exclude, int $limit = 3): array
    {
        $artists = $this->em->getRepository(Artist::class)->findAll();
        $names = array_map(fn($a) => $a->getName(), $artists);
        $names = array_diff($names, [$exclude]);
        shuffle($names);
        return array_slice($names, 0, $limit);
    }

    private function addAlbumArtistQuestion(array &$questions, Artist $artist): void
    {
        $albums = $artist->getAlbums()->toArray();
        if (empty($albums)) return;

        // Choisis un album au hasard de l'artiste
        $album = $albums[array_rand($albums)];
        $albumTitle = $album->getTitle();
        $correctArtistName = $artist->getName();

        // Récupérer d'autres albums pour trouver d'autres artistes
        $allAlbums = $this->em->getRepository(Album::class)->findAll();
        $wrongArtists = [];
        foreach ($allAlbums as $a) {
            if ($a->getTitle() === $albumTitle) continue; // Exclut l'album choisi
            $artistName = $a->getArtist()?->getName();
            if ($artistName && $artistName !== $correctArtistName) {
                $wrongArtists[] = $artistName;
            }
        }

        shuffle($wrongArtists);
        $wrongArtists = array_slice($wrongArtists, 0, 3);

        $this->addQuestionIfNotExists(
            $questions,
            "Quel groupe a fait l'album « {$albumTitle} » ?",
            $correctArtistName,
            $wrongArtists,
            'album_artist',
            2,
            $artist
        );
    }

    private function addWhichAlbumIsArtistQuestion(array &$questions, Artist $artist): void
    {
        $albums = $artist->getAlbums()->toArray();
        if (empty($albums)) return;

        // Choisis un album au hasard de l'artiste
        $album = $albums[array_rand($albums)];
        $albumTitle = $album->getTitle();
        $correctArtistName = $artist->getName();

        // Récupérer d'autres albums pour trouver d'autres artistes
        $allAlbums = $this->em->getRepository(Album::class)->findAll();
        $wrongAlbums = [];
        foreach ($allAlbums as $a) {
            if ($a->getArtist()?->getId() === $artist->getId()) continue; // exclut l'artiste courant
            $wrongAlbums[] = $a->getTitle();
        }

        shuffle($wrongAlbums);
        $wrongAlbums = array_slice($wrongAlbums, 0, 3); // on prend 3 albums d'autres artistes

        $this->addQuestionIfNotExists(
            $questions,
            "Lequel de ces albums est celui de « {$artist->getName()} » ?",
            $albumTitle,
            $wrongAlbums,
            'album_artist',
            2,
            $artist
        );
    }



    private function getRandomAlbumTitlesForArtist(Artist $artist, string $excludeTitle): array
    {
        $titles = [];

        foreach ($artist->getAlbums() as $album) {
            if ($album->getTitle() !== $excludeTitle) {
                $titles[] = $album->getTitle();
            }
        }

        shuffle($titles);

        return array_slice($titles, 0, 3);
    }

    private function getRandomYears(int $excludeYear, int $limit = 3): array
    {
        $years = range($excludeYear - 20, $excludeYear + 20);
        $years = array_diff($years, [$excludeYear]);
        shuffle($years);
        return array_slice($years, 0, $limit);
    }

    private function getRandomCountryNames(string $exclude, int $limit = 3): array
    {
        $countries = $this->em->getRepository(Country::class)->findAll();
        $names = array_map(fn($c) => $c->getName(), $countries);
        $names = array_diff($names, [$exclude]);
        shuffle($names);
        return array_slice($names, 0, $limit);
    }

    private function getRandomGenres(string $exclude, int $limit = 3): array
    {
        $allGenres = $this->em->getRepository(\App\Entity\Genre::class)->findAll();
        $names = array_map(fn($g) => $g->getName(), $allGenres);
        $names = array_diff($names, [$exclude]);
        shuffle($names);
        return array_slice($names, 0, $limit);
    }

    private function getRandomCityNames(string $exclude, int $limit = 3): array
    {
        $cities = $this->em->getRepository(\App\Entity\City::class)->findAll();
        $names = array_map(fn($c) => $c->getName(), $cities);
        $names = array_diff($names, [$exclude]);
        shuffle($names);
        return array_slice($names, 0, $limit);
    }
}
