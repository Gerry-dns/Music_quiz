<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201165747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE mbid mbid VARCHAR(36) DEFAULT NULL, CHANGE country country VARCHAR(100) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(500) DEFAULT NULL, CHANGE albums albums JSON DEFAULT NULL, CHANGE members members JSON DEFAULT NULL, CHANGE main_genre main_genre VARCHAR(100) DEFAULT NULL, CHANGE sub_genres sub_genres JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE difficulty difficulty INT DEFAULT NULL, CHANGE category category VARCHAR(100) DEFAULT NULL, CHANGE played_count played_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT \'NULL\', CHANGE mbid mbid VARCHAR(36) DEFAULT \'NULL\', CHANGE country country VARCHAR(100) DEFAULT \'NULL\', CHANGE main_genre main_genre VARCHAR(100) DEFAULT \'NULL\', CHANGE sub_genres sub_genres LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE cover_image cover_image VARCHAR(500) DEFAULT \'NULL\', CHANGE albums albums LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE members members LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions CHANGE difficulty difficulty INT NOT NULL, CHANGE category category VARCHAR(100) DEFAULT \'NULL\', CHANGE played_count played_count INT NOT NULL');
    }
}
