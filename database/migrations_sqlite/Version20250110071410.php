<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110071410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add colors to clubs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_clubs ADD COLUMN themeColor VARCHAR(255) DEFAULT \'green\' NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, google_calendar_id VARCHAR(255) NOT NULL, google_calendar_url VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL)');
        $this->addSql('INSERT INTO orm_events (id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type) SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_clubs AS SELECT slug, name FROM orm_clubs');
        $this->addSql('DROP TABLE orm_clubs');
        $this->addSql('CREATE TABLE orm_clubs (slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(slug))');
        $this->addSql('INSERT INTO orm_clubs (slug, name) SELECT slug, name FROM __temp__orm_clubs');
        $this->addSql('DROP TABLE __temp__orm_clubs');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, google_calendar_id VARCHAR(255) DEFAULT NULL, google_calendar_url VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL)');
        $this->addSql('INSERT INTO orm_events (id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type) SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
    }
}
