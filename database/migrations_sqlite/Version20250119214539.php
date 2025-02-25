<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250119214539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Google calendar in db';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_clubs ADD COLUMN google_calendar_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_clubs ADD COLUMN google_credential_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_clubs AS SELECT slug, name, themeColor FROM orm_clubs');
        $this->addSql('DROP TABLE orm_clubs');
        $this->addSql('CREATE TABLE orm_clubs (slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, themeColor VARCHAR(255) DEFAULT \'green\' NOT NULL, PRIMARY KEY(slug))');
        $this->addSql('INSERT INTO orm_clubs (slug, name, themeColor) SELECT slug, name, themeColor FROM __temp__orm_clubs');
        $this->addSql('DROP TABLE __temp__orm_clubs');
    }
}
