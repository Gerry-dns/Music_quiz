<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123140510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, mbid VARCHAR(36) DEFAULT NULL, genre VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, founded_year INT DEFAULT NULL, cover_image VARCHAR(500) DEFAULT NULL, biography LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, correct_answer VARCHAR(255) NOT NULL, wrong_answer1 VARCHAR(255) NOT NULL, wrong_answer2 VARCHAR(255) NOT NULL, wrong_answer3 VARCHAR(255) NOT NULL, difficulty INT NOT NULL, category VARCHAR(100) DEFAULT NULL, year_hint INT DEFAULT NULL, explanation LONGTEXT DEFAULT NULL, played_count INT NOT NULL, correct_count INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_8ADC54D5B7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5B7970CF8');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
