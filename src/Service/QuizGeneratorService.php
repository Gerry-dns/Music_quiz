<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Questions;
use Doctrine\ORM\EntityManagerInterface;

class QuizGeneratorService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

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
            $question->setText("Qui a sorti l'album « {$album} » ?");
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

        // --- 2️⃣ Question Année de formation ---
        if ($artist->getFoundedYear()) {
            $year = $artist->getFoundedYear();
            $question = new Questions();
            $question->setText("En quelle année s'est formé « {$artist->getName()} » ?");
            $question->setCorrectAnswer((string) $year);
            $question->setWrongAnswer1((string) ($year + 2));
            $question->setWrongAnswer2((string) ($year - 6));
            $question->setWrongAnswer3((string) ($year + 5));
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
            $question->setWrongAnswer1($this->getRandomGenre($artist->getMainGenre()));
            $question->setWrongAnswer2($this->getRandomGenre($artist->getMainGenre()));
            $question->setWrongAnswer3($this->getRandomGenre($artist->getMainGenre()));
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
            $question->setText("Où a débuté le groupe « {$artist->getName()} » ?");
            $question->setCorrectAnswer($beginArea);

            $wrongAreas = $this->getRandomAreas($beginArea, 3);
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

    // -----------------------------
    // Génération de réponses aléatoires
    // -----------------------------
    private function getRandomAreas(string $correct, int $count = 3): array
    {
        $allAreas = [
            'London', 'New York', 'Los Angeles', 'Paris', 'Berlin',
            'Tokyo', 'São Paulo', 'Mexico City', 'Toronto', 'Sydney',
            'Madrid', 'Rome', 'Chicago', 'Dublin', 'Amsterdam',
            'Stockholm', 'Lisbon', 'Melbourne'
        ];

        $areas = array_diff($allAreas, [$correct]);
        shuffle($areas);

        return array_slice($areas, 0, $count);
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

    private function getRandomGenre(string $exclude): string
    {
        $genres = ['rock', 'pop', 'jazz', 'electronic', 'hip hop', 'indie', 'folk', 'metal', 'blues'];
        $genres = array_filter($genres, fn($g) => $g !== $exclude);

        if (empty($genres)) {
            return 'Genre Aléatoire';
        }

        return $genres[array_rand($genres)];
    }
}
