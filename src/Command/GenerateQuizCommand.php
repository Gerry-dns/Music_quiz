<?php

namespace App\Command;

use App\Entity\Artist;
use App\Service\QuizGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-quiz',
    description: 'Génère automatiquement les questions pour un artiste donné.'
)]
class GenerateQuizCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private QuizGeneratorService $quizService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('artistId', InputArgument::REQUIRED, 'ID de l’artiste');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $artistId = $input->getArgument('artistId');
        $artist = $this->em->getRepository(Artist::class)->find($artistId);

        if (!$artist) {
            $output->writeln('Artiste introuvable.');
            return Command::FAILURE;
        }

        $this->quizService->generateQuestionsForArtist($artist);
        $output->writeln('Questions générées pour ' . $artist->getName());

        return Command::SUCCESS;
    }
}
