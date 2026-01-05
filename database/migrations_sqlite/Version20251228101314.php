<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228101314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add start_date and end_date to activities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_activities AS SELECT id, event_id, type, date, deadline, open, name, location_label, location_url, description FROM orm_activities');
        $this->addSql('DROP TABLE orm_activities');
        $this->addSql('CREATE TABLE orm_activities (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, deadline DATETIME NOT NULL, open BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, location_label VARCHAR(255) NOT NULL, location_url VARCHAR(255) NOT NULL, description CLOB NOT NULL, end_date DATETIME NOT NULL, CONSTRAINT FK_DA9A084071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_activities (id, event_id, type, start_date, deadline, open, name, location_label, location_url, description, end_date) SELECT id, event_id, type, date, deadline, open, name, location_label, location_url, description, date FROM __temp__orm_activities');
        $this->addSql('DROP TABLE __temp__orm_activities');
        $this->addSql('CREATE INDEX IDX_DA9A084071F7E88B ON orm_activities (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_activities AS SELECT id, event_id, type, deadline, open, name, location_label, location_url, description, start_date FROM orm_activities');
        $this->addSql('DROP TABLE orm_activities');
        $this->addSql('CREATE TABLE orm_activities (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, deadline DATETIME NOT NULL, open BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, location_label VARCHAR(255) NOT NULL, location_url VARCHAR(255) NOT NULL, description CLOB NOT NULL, date DATETIME NOT NULL, CONSTRAINT FK_DA9A084071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_activities (id, event_id, type, deadline, open, name, location_label, location_url, description, date) SELECT id, event_id, type, deadline, open, name, location_label, location_url, description, start_date FROM __temp__orm_activities');
        $this->addSql('DROP TABLE __temp__orm_activities');
        $this->addSql('CREATE INDEX IDX_DA9A084071F7E88B ON orm_activities (event_id)');
    }
}
