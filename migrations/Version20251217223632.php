<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217223632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_decade (artist_id INT NOT NULL, decade_id INT NOT NULL, INDEX IDX_DDB8DA22B7970CF8 (artist_id), INDEX IDX_DDB8DA22FF312AC0 (decade_id), PRIMARY KEY (artist_id, decade_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_instrument (artist_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_DFC0B6A4B7970CF8 (artist_id), INDEX IDX_DFC0B6A4CF11D9C (instrument_id), PRIMARY KEY (artist_id, instrument_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT FK_DDB8DA22B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT FK_DDB8DA22FF312AC0 FOREIGN KEY (decade_id) REFERENCES decade (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_instrument ADD CONSTRAINT FK_DFC0B6A4B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_instrument ADD CONSTRAINT FK_DFC0B6A4CF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE mbid mbid VARCHAR(36) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE albums albums JSON DEFAULT NULL, CHANGE members members JSON DEFAULT NULL, CHANGE sub_genres sub_genres JSON DEFAULT NULL, CHANGE begin_area begin_area VARCHAR(255) DEFAULT NULL, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE life_span life_span JSON DEFAULT NULL, CHANGE biography biography JSON DEFAULT NULL, CHANGE urls urls JSON DEFAULT NULL, CHANGE tracks tracks JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE iso_code iso_code VARCHAR(5) DEFAULT NULL, CHANGE flag flag VARCHAR(10) DEFAULT NULL, CHANGE continent continent VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY FK_DDB8DA22B7970CF8');
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY FK_DDB8DA22FF312AC0');
        $this->addSql('ALTER TABLE artist_instrument DROP FOREIGN KEY FK_DFC0B6A4B7970CF8');
        $this->addSql('ALTER TABLE artist_instrument DROP FOREIGN KEY FK_DFC0B6A4CF11D9C');
        $this->addSql('DROP TABLE artist_decade');
        $this->addSql('DROP TABLE artist_instrument');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT \'NULL\', CHANGE mbid mbid VARCHAR(36) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE albums albums LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE tracks tracks LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE members members LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE biography biography LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE sub_genres sub_genres LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE begin_area begin_area VARCHAR(255) DEFAULT \'NULL\', CHANGE life_span life_span LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE urls urls LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE iso_code iso_code VARCHAR(5) DEFAULT \'NULL\', CHANGE flag flag VARCHAR(10) DEFAULT \'NULL\', CHANGE continent continent VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE slug slug VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT \'NULL\'');
    }
}
