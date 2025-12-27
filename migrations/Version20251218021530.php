<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218021530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `release` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(55) DEFAULT NULL, release_date DATE DEFAULT NULL, artist_id INT DEFAULT NULL, INDEX IDX_9E47031DB7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, release_tracks_id INT DEFAULT NULL, INDEX IDX_D6E3F8A632142E26 (release_tracks_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `release` ADD CONSTRAINT FK_9E47031DB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A632142E26 FOREIGN KEY (release_tracks_id) REFERENCES `release` (id)');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE mbid mbid VARCHAR(36) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE albums albums JSON DEFAULT NULL, CHANGE members members JSON DEFAULT NULL, CHANGE sub_genres sub_genres JSON DEFAULT NULL, CHANGE begin_area begin_area VARCHAR(255) DEFAULT NULL, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE life_span life_span JSON DEFAULT NULL, CHANGE biography biography JSON DEFAULT NULL, CHANGE urls urls JSON DEFAULT NULL, CHANGE tracks tracks JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE iso_code iso_code VARCHAR(5) DEFAULT NULL, CHANGE flag flag VARCHAR(10) DEFAULT NULL, CHANGE continent continent VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY `FK_8ADC54D5B7970CF8`');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `release` DROP FOREIGN KEY FK_9E47031DB7970CF8');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A632142E26');
        $this->addSql('DROP TABLE `release`');
        $this->addSql('DROP TABLE track');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT \'NULL\', CHANGE mbid mbid VARCHAR(36) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE albums albums LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE tracks tracks LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE members members LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE biography biography LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE sub_genres sub_genres LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE begin_area begin_area VARCHAR(255) DEFAULT \'NULL\', CHANGE life_span life_span LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE urls urls LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE iso_code iso_code VARCHAR(5) DEFAULT \'NULL\', CHANGE flag flag VARCHAR(10) DEFAULT \'NULL\', CHANGE continent continent VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE slug slug VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5B7970CF8');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT `FK_8ADC54D5B7970CF8` FOREIGN KEY (artist_id) REFERENCES artist (id)');
    }
}
