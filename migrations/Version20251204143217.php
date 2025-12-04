<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204143217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE instrument (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_3CBF69DD5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY `FK_DDB8DA22B7970CF8`');
        $this->addSql('ALTER TABLE artist_decade DROP FOREIGN KEY `FK_DDB8DA22FF312AC0`');
        $this->addSql('DROP TABLE artist_decade');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) NOT NULL, CHANGE mbid mbid VARCHAR(36) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE albums albums JSON DEFAULT NULL, CHANGE members members JSON DEFAULT NULL, CHANGE sub_genres sub_genres JSON DEFAULT NULL, CHANGE begin_area begin_area VARCHAR(255) DEFAULT NULL, CHANGE youtube_url youtube_url VARCHAR(255) DEFAULT NULL, CHANGE wikidata_url wikidata_url VARCHAR(255) DEFAULT NULL, CHANGE spotify_url spotify_url VARCHAR(255) DEFAULT NULL, CHANGE deezer_url deezer_url VARCHAR(255) DEFAULT NULL, CHANGE bandcamp_url bandcamp_url VARCHAR(255) DEFAULT NULL, CHANGE discogs_url discogs_url VARCHAR(255) DEFAULT NULL, CHANGE official_site_url official_site_url VARCHAR(255) DEFAULT NULL, CHANGE soundcloud_url soundcloud_url VARCHAR(255) DEFAULT NULL, CHANGE lastfm_url lastfm_url VARCHAR(255) DEFAULT NULL, CHANGE twitter_url twitter_url VARCHAR(255) DEFAULT NULL, CHANGE facebook_url facebook_url VARCHAR(255) DEFAULT NULL, CHANGE instagram_url instagram_url VARCHAR(255) DEFAULT NULL, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE iso_code iso_code VARCHAR(5) DEFAULT NULL, CHANGE flag flag VARCHAR(10) DEFAULT NULL, CHANGE continent continent VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_decade (artist_id INT NOT NULL, decade_id INT NOT NULL, INDEX IDX_DDB8DA22B7970CF8 (artist_id), INDEX IDX_DDB8DA22FF312AC0 (decade_id), PRIMARY KEY (artist_id, decade_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT `FK_DDB8DA22B7970CF8` FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_decade ADD CONSTRAINT `FK_DDB8DA22FF312AC0` FOREIGN KEY (decade_id) REFERENCES decade (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE instrument');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) DEFAULT \'NULL\', CHANGE mbid mbid VARCHAR(36) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(500) DEFAULT \'NULL\', CHANGE albums albums LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE members members LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE sub_genres sub_genres LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE begin_area begin_area VARCHAR(255) DEFAULT \'NULL\', CHANGE youtube_url youtube_url VARCHAR(255) DEFAULT \'NULL\', CHANGE wikidata_url wikidata_url VARCHAR(255) DEFAULT \'NULL\', CHANGE spotify_url spotify_url VARCHAR(255) DEFAULT \'NULL\', CHANGE deezer_url deezer_url VARCHAR(255) DEFAULT \'NULL\', CHANGE bandcamp_url bandcamp_url VARCHAR(255) DEFAULT \'NULL\', CHANGE discogs_url discogs_url VARCHAR(255) DEFAULT \'NULL\', CHANGE official_site_url official_site_url VARCHAR(255) DEFAULT \'NULL\', CHANGE soundcloud_url soundcloud_url VARCHAR(255) DEFAULT \'NULL\', CHANGE lastfm_url lastfm_url VARCHAR(255) DEFAULT \'NULL\', CHANGE twitter_url twitter_url VARCHAR(255) DEFAULT \'NULL\', CHANGE facebook_url facebook_url VARCHAR(255) DEFAULT \'NULL\', CHANGE instagram_url instagram_url VARCHAR(255) DEFAULT \'NULL\', CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE iso_code iso_code VARCHAR(5) DEFAULT \'NULL\', CHANGE flag flag VARCHAR(10) DEFAULT \'NULL\', CHANGE continent continent VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE slug slug VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT \'NULL\'');
    }
}
