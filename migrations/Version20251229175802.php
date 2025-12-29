<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229175802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, mbid VARCHAR(36) DEFAULT NULL, first_release_date DATE DEFAULT NULL, artist_id INT NOT NULL, INDEX IDX_39986E43B7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, mbid VARCHAR(36) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, biography JSON DEFAULT NULL, urls JSON DEFAULT NULL, country_id INT DEFAULT NULL, begin_area_id INT DEFAULT NULL, main_genre_id INT DEFAULT NULL, INDEX IDX_1599687F92F3E70 (country_id), INDEX IDX_1599687BDF3BA30 (begin_area_id), INDEX IDX_15996879BB4C26A (main_genre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_decade (artist_id INT NOT NULL, decade_id INT NOT NULL, INDEX IDX_DDB8DA22B7970CF8 (artist_id), INDEX IDX_DDB8DA22FF312AC0 (decade_id), PRIMARY KEY (artist_id, decade_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_instrument (id INT AUTO_INCREMENT NOT NULL, count INT DEFAULT NULL, artist_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_DFC0B6A4B7970CF8 (artist_id), INDEX IDX_DFC0B6A4CF11D9C (instrument_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_member (id INT AUTO_INCREMENT NOT NULL, begin VARCHAR(10) DEFAULT NULL, end VARCHAR(10) DEFAULT NULL, is_original TINYINT(1) NOT NULL, artist_id INT NOT NULL, member_id INT NOT NULL, INDEX IDX_82E6F606B7970CF8 (artist_id), INDEX IDX_82E6F6067597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_member_instrument (id INT AUTO_INCREMENT NOT NULL, is_primary TINYINT(1) NOT NULL, member_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_F221D5FE7597D3FE (member_id), INDEX IDX_F221D5FECF11D9C (instrument_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist_sub_genre (id INT AUTO_INCREMENT NOT NULL, count INT DEFAULT 1 NOT NULL, artist_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_73C03CC9B7970CF8 (artist_id), INDEX IDX_73C03CC94296D31F (genre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, country_id INT NOT NULL, INDEX IDX_2D5B0234F92F3E70 (country_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, iso_code VARCHAR(5) DEFAULT NULL, flag VARCHAR(10) DEFAULT NULL, continent VARCHAR(50) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE decade (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instrument (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_3CBF69DD5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE member_instrument (id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_D7F177347597D3FE (member_id), INDEX IDX_D7F17734CF11D9C (instrument_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, correct_answer VARCHAR(255) NOT NULL, wrong_answer1 VARCHAR(255) NOT NULL, wrong_answer2 VARCHAR(255) NOT NULL, wrong_answer3 VARCHAR(255) NOT NULL, difficulty INT DEFAULT NULL, category VARCHAR(100) DEFAULT NULL, year_hint INT DEFAULT NULL, explanation LONGTEXT DEFAULT NULL, played_count INT DEFAULT NULL, correct_count INT DEFAULT NULL, artist_id INT DEFAULT NULL, INDEX IDX_8ADC54D5B7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `release` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(55) DEFAULT NULL, release_date DATE DEFAULT NULL, artist_id INT DEFAULT NULL, INDEX IDX_9E47031DB7970CF8 (artist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, release_tracks_id INT DEFAULT NULL, INDEX IDX_D6E3F8A632142E26 (release_tracks_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687BDF3BA30 FOREIGN KEY (begin_area_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_15996879BB4C26A FOREIGN KEY (main_genre_id) REFERENCES genre (id)');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT FK_DDB8DA22B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT FK_DDB8DA22FF312AC0 FOREIGN KEY (decade_id) REFERENCES decade (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_instrument ADD CONSTRAINT FK_DFC0B6A4B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE artist_instrument ADD CONSTRAINT FK_DFC0B6A4CF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id)');
        $this->addSql('ALTER TABLE artist_member ADD CONSTRAINT FK_82E6F606B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE artist_member ADD CONSTRAINT FK_82E6F6067597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE artist_member_instrument ADD CONSTRAINT FK_F221D5FE7597D3FE FOREIGN KEY (member_id) REFERENCES artist_member (id)');
        $this->addSql('ALTER TABLE artist_member_instrument ADD CONSTRAINT FK_F221D5FECF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id)');
        $this->addSql('ALTER TABLE artist_sub_genre ADD CONSTRAINT FK_73C03CC9B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE artist_sub_genre ADD CONSTRAINT FK_73C03CC94296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE member_instrument ADD CONSTRAINT FK_D7F177347597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE member_instrument ADD CONSTRAINT FK_D7F17734CF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `release` ADD CONSTRAINT FK_9E47031DB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A632142E26 FOREIGN KEY (release_tracks_id) REFERENCES `release` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43B7970CF8');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_1599687F92F3E70');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_1599687BDF3BA30');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_15996879BB4C26A');
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY FK_DDB8DA22B7970CF8');
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY FK_DDB8DA22FF312AC0');
        $this->addSql('ALTER TABLE artist_instrument DROP FOREIGN KEY FK_DFC0B6A4B7970CF8');
        $this->addSql('ALTER TABLE artist_instrument DROP FOREIGN KEY FK_DFC0B6A4CF11D9C');
        $this->addSql('ALTER TABLE artist_member DROP FOREIGN KEY FK_82E6F606B7970CF8');
        $this->addSql('ALTER TABLE artist_member DROP FOREIGN KEY FK_82E6F6067597D3FE');
        $this->addSql('ALTER TABLE artist_member_instrument DROP FOREIGN KEY FK_F221D5FE7597D3FE');
        $this->addSql('ALTER TABLE artist_member_instrument DROP FOREIGN KEY FK_F221D5FECF11D9C');
        $this->addSql('ALTER TABLE artist_sub_genre DROP FOREIGN KEY FK_73C03CC9B7970CF8');
        $this->addSql('ALTER TABLE artist_sub_genre DROP FOREIGN KEY FK_73C03CC94296D31F');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234F92F3E70');
        $this->addSql('ALTER TABLE member_instrument DROP FOREIGN KEY FK_D7F177347597D3FE');
        $this->addSql('ALTER TABLE member_instrument DROP FOREIGN KEY FK_D7F17734CF11D9C');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5B7970CF8');
        $this->addSql('ALTER TABLE `release` DROP FOREIGN KEY FK_9E47031DB7970CF8');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A632142E26');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE artist_decade');
        $this->addSql('DROP TABLE artist_instrument');
        $this->addSql('DROP TABLE artist_member');
        $this->addSql('DROP TABLE artist_member_instrument');
        $this->addSql('DROP TABLE artist_sub_genre');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE decade');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE instrument');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE member_instrument');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE `release`');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
