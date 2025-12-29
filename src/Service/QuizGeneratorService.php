<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Country;
use App\Entity\Questions;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GenreRepository;

class QuizGeneratorService
{

    private EntityManagerInterface $em;
    private GenreRepository $genreRepository;
    public function __construct(EntityManagerInterface $em, GenreRepository $genreRepository)
    {
        $this->em = $em;
        $this->genreRepository = $genreRepository;
    }
    /**
     * Génère automatiquement des questions pour un artiste donné.
     */
    public function generateQuestionsForArtist(Artist $artist): void
    {
        $questions = [];

        // --- 1️⃣ Questions Albums --- 5 premiers albums
        $albums = array_slice($artist->getAlbums(), 0, 1);

        foreach ($albums as $album) {
            $wrongAnswers = $this->getRandomArtistsNames($artist->getName(), 3);

            $question = new Questions();
            $question->setText("Qui a sorti l'album « {$album['title']} » ?");
            $question->setCorrectAnswer($artist->getName());
            $question->setWrongAnswer1($wrongAnswers[0] ?? 'Artiste inconnu');
            $question->setWrongAnswer2($wrongAnswers[1] ?? 'Artiste inconnu');
            $question->setWrongAnswer3($wrongAnswers[2] ?? 'Artiste inconnu');
            $question->setDifficulty(2);
            $question->setCategory('album');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        $questions = [];

        // --- 1️⃣ Question Premier album ---
        $firstAlbum = $this->em->getRepository(Artist::class)->getFirstAlbum($artist);
        if ($firstAlbum) {
            $wrongAlbums = $this->em->getRepository(Artist::class)->getRandomAlbums($firstAlbum, 3);

            $question = new Questions();
            $question->setText("Quel est le premier album sorti par « {$artist->getName()} » ?");
            $question->setCorrectAnswer($firstAlbum);
            $question->setWrongAnswer1($wrongAlbums[0] ?? 'Album inconnu');
            $question->setWrongAnswer2($wrongAlbums[1] ?? 'Album inconnu');
            $question->setWrongAnswer3($wrongAlbums[2] ?? 'Album inconnu');
            $question->setDifficulty(2);
            $question->setCategory('album');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- 2️⃣ Question Dernier album ---
        $lastAlbum = $this->em->getRepository(Artist::class)->getLastAlbum($artist);
        if ($lastAlbum && $lastAlbum !== $firstAlbum) {
            $wrongAlbums = $this->em->getRepository(Artist::class)->getRandomAlbums($lastAlbum, 3);

            $question = new Questions();
            $question->setText("Quel est le dernier album sorti par « {$artist->getName()} » ?");
            $question->setCorrectAnswer($lastAlbum);
            $question->setWrongAnswer1($wrongAlbums[0] ?? 'Album inconnu');
            $question->setWrongAnswer2($wrongAlbums[1] ?? 'Album inconnu');
            $question->setWrongAnswer3($wrongAlbums[2] ?? 'Album inconnu');
            $question->setDifficulty(2);
            $question->setCategory('album');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- 3️⃣ Question Pays ---
        $country = $artist->getCountry()?->getName();
        if ($country) {
            $allCountries = $this->em->getRepository(Country::class)->findAll();
            $wrongCountries = array_filter(
                array_map(fn($c) => $c->getName(), $allCountries),
                fn($name) => $name !== $country
            );
            shuffle($wrongCountries);

            $question = new Questions();
            $question->setText("De quel pays vient « {$artist->getName()} » ?");
            $question->setCorrectAnswer($country);
            $question->setWrongAnswer1($wrongCountries[0] ?? 'Inconnu');
            $question->setWrongAnswer2($wrongCountries[1] ?? 'Inconnu');
            $question->setWrongAnswer3($wrongCountries[2] ?? 'Inconnu');
            $question->setDifficulty(1);
            $question->setCategory('country');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- Persister toutes les questions ---
        foreach ($questions as $q) {
            $this->em->persist($q);
        }
        $this->em->flush();



        // --- 2️⃣ Question Année de formation ---
        if ($artist->getBeginYear()) {
            $fullYear = $artist->getBeginYear(); // ex: "1990-04-30"
            $year = intval(substr($fullYear, 0, 4)); // ne garde que "1990" comme int
            $question = new Questions();
            $question->setText("En quelle année s'est formé « {$artist->getName()} » ?");
            $question->setCorrectAnswer($year);
            $question->setWrongAnswer1(($year + 10));
            $question->setWrongAnswer2(($year - 8));
            $question->setWrongAnswer3(($year + 5));
            $question->setDifficulty(2);
            $question->setCategory('année');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- 3️⃣ Question Genre principal ---
        if ($artist->getMainGenre()) {
            $question = new Questions();
            $question->setText("Quel est le genre principal de « {$artist->getName()} » ?");
            $question->setCorrectAnswer($artist->getMainGenre());
            $question->setWrongAnswer1($this->genreRepository->getRandomGenre($artist->getMainGenre()));
            $question->setWrongAnswer2($this->genreRepository->getRandomGenre($artist->getMainGenre()));
            $question->setWrongAnswer3($this->genreRepository->getRandomGenre($artist->getMainGenre()));
            $question->setDifficulty(1);
            $question->setCategory('genre');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- 4️⃣ Question Lieu de début (begin-area) ---
        $beginArea = $artist->getBeginArea(); // "London"
        if ($beginArea) {
            $question = new Questions();
            $question->setText("Dans quell ville a débuté le groupe « {$artist->getName()} » ?");
            $question->setCorrectAnswer($beginArea);

            $allAreas = $this->em->getRepository(Artist::class)->findDistinctBeginAreas();
            $wrongAreas = array_diff($allAreas, [$beginArea]);
            shuffle($wrongAreas);
            $question->setWrongAnswer1($wrongAreas[0] ?? 'Inconnue');
            $question->setWrongAnswer2($wrongAreas[1] ?? 'Inconnue');
            $question->setWrongAnswer3($wrongAreas[2] ?? 'Inconnue');

            $question->setDifficulty(1);
            $question->setCategory('begin-area');
            $question->setArtist($artist);
            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // --- 5️⃣ Question Membres du groupe ---
        $members = $artist->getMembers(); // chaque membre = ['name' => ..., 'instruments' => ...]
        if (!empty($members)) {
            $randomKey = array_rand($members);
            $member = $members[$randomKey];

            $memberName = $member['name'] ?? null;
            if ($memberName) {
                $wrongAnswers = $this->getRandomArtistsNames($artist->getName(), 3);

                $question = new Questions();
                $question->setText("{$memberName} est le membre du groupe :");
                $question->setCorrectAnswer($artist->getName());
                $question->setWrongAnswer1($wrongAnswers[0] ?? 'Artiste inconnu');
                $question->setWrongAnswer2($wrongAnswers[1] ?? 'Artiste inconnu');
                $question->setWrongAnswer3($wrongAnswers[2] ?? 'Artiste inconnu');
                $question->setDifficulty(2);
                $question->setCategory('membre');
                $question->setArtist($artist);
                $question->setPlayedCount(0);
                $question->setCorrectCount(0);

                $questions[] = $question;
            }
        }

        // --- Persister toutes les questions ---
        foreach ($questions as $q) {
            $this->em->persist($q);
        }
        $this->em->flush();
    }



    private function getRandomArtistsNames(string $exclude, int $count = 3): array
    {
        $artists = $this->em->getRepository(Artist::class)
            ->createQueryBuilder('a')
            ->where('a.name != :name')
            ->setParameter('name', $exclude)
            ->getQuery()
            ->getResult();

        $names = array_map(fn($a) => $a->getName(), $artists);
        shuffle($names);

        return array_slice($names, 0, $count);
    }
}
