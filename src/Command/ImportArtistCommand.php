<?php

namespace App\Command;

use App\Entity\Artist;
use App\Service\ArtistImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-artist',
    description: 'Importe un artiste depuis MusicBrainz via son MBID.'
)]
class ImportArtistCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ArtistImporterService $importerService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('mbid', InputArgument::REQUIRED, 'Le MBID de l’artiste à importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mbid = $input->getArgument('mbid');

        // Vérifie si l'artiste existe déjà en base
        $existingArtist = $this->em->getRepository(Artist::class)->findOneBy(['mbid' => $mbid]);
        if ($existingArtist) {
            $output->writeln('Artiste déjà présent : ' . $existingArtist->getName());
            return Command::SUCCESS;
        }

        // Importer l'artiste via le service
        try {
            $artist = $this->importerService->importFromMBID($mbid);
            $this->em->persist($artist);
            $this->em->flush();

            $output->writeln('Artiste importé : ' . $artist->getName());
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Erreur lors de l’import : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
