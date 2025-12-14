<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212022943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist CHANGE mbid mbid VARCHAR(36) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE albums albums JSON DEFAULT NULL, CHANGE members members JSON DEFAULT NULL, CHANGE sub_genres sub_genres JSON DEFAULT NULL, CHANGE begin_area begin_area VARCHAR(255) DEFAULT NULL, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE life_span life_span JSON DEFAULT NULL, CHANGE biography biography JSON DEFAULT NULL, CHANGE urls urls JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE iso_code iso_code VARCHAR(5) DEFAULT NULL, CHANGE flag flag VARCHAR(10) DEFAULT NULL, CHANGE continent continent VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist CHANGE mbid mbid VARCHAR(36) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE albums albums LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE members members LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE biography biography LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE sub_genres sub_genres LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE begin_area begin_area VARCHAR(255) DEFAULT \'NULL\', CHANGE life_span life_span LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE urls urls LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE disambiguation disambiguation VARCHAR(255) DEFAULT \'NULL\', CHANGE type type VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE iso_code iso_code VARCHAR(5) DEFAULT \'NULL\', CHANGE flag flag VARCHAR(10) DEFAULT \'NULL\', CHANGE continent continent VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE decade CHANGE name name VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE genre CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE slug slug VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE questions CHANGE category category VARCHAR(100) DEFAULT \'NULL\'');
    }
}
