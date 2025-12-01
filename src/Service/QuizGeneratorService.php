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
        // --- 1️⃣ Questions Albums --- 5 premières albums
        $albums = array_slice($artist->getAlbums(), 0, 2);

        foreach ($albums as $album) {
            $question = new Questions();
            $question->setText("Qui a sorti l'album « {$album} » ?");
            $question->setCorrectAnswer($artist->getName());
            $question->setWrongAnswer1($this->getRandomArtistName($artist->getName()));
            $question->setWrongAnswer2($this->getRandomArtistName($artist->getName()));
            $question->setWrongAnswer3($this->getRandomArtistName($artist->getName()));
            $question->setDifficulty(2);
            $question->setCategory('album');
            $question->setArtist($artist);

            $question->setPlayedCount(0);
            $question->setCorrectCount(0);

            $questions[] = $question;
        }

        // // --- 2️⃣ Questions Membres/Instrument ---
        // foreach ($artist->getMembers() as $member) {
        //     foreach ($member['instruments'] ?? [] as $instrument) {
        //         $question = new Questions();
        //         $question->setText("Qui joue du {$instrument} dans « {$artist->getName()} » ?");
        //         $question->setCorrectAnswer($member['name']);
        //         $question->setWrongAnswer1($this->getRandomMemberName($member['name']));
        //         $question->setWrongAnswer2($this->getRandomMemberName($member['name']));
        //         $question->setWrongAnswer3($this->getRandomMemberName($member['name']));
        //         $question->setDifficulty(3);
        //         $question->setCategory('membre');
        //         $question->setArtist($artist);
        //         $questions[] = $question;
        //     }
        // }

        // --- 3️⃣ Question Année de formation ---
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

        // --- 4️⃣ Question Genre ---
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

        // --- Persister toutes les questions ---
        foreach ($questions as $q) {
            $this->em->persist($q);
        }
        $this->em->flush();
    }

    // -----------------------------
    // Méthodes pour générer des réponses aléatoires
    // -----------------------------
    private function getRandomArtistName(string $exclude): string
    {
        $artists = $this->em->getRepository(Artist::class)
            ->createQueryBuilder('a')
            ->where('a.name != :name')
            ->setParameter('name', $exclude)
            ->getQuery()
            ->getResult();

        if (!$artists) {
            return 'Artiste Aléatoire';
        }

        $randomArtist = $artists[array_rand($artists)];
        return $randomArtist->getName();
    }



    // private function getRandomMemberName(string $exclude): string
    // {
    //     // Pour simplifier, on peut prendre un membre aléatoire depuis tous les artistes
    //     $members = [];
    //     $allArtists = $this->em->getRepository(Artist::class)->findAll();

    //     foreach ($allArtists as $artist) {
    //         foreach ($artist->getMembers() as $member) {
    //             if ($member['name'] !== $exclude) {
    //                 $members[] = $member['name'];
    //             }
    //         }
    //     }

    //     if (!$members) {
    //         return 'Membre Aléatoire';
    //     }

    //     return $members[array_rand($members)];
    // }

    private function getRandomGenre(string $exclude): string
    {
        // Liste fixe de genres, à remplacer par une table Genres si tu veux plus tard
        $genres = ['rock', 'pop', 'jazz', 'electronic', 'hip hop', 'indie', 'folk', 'metal', 'blues'];
        $genres = array_filter($genres, fn($g) => $g !== $exclude);

        if (empty($genres)) {
            return 'Genre Aléatoire';
        }

        return $genres[array_rand($genres)];
    }
}
