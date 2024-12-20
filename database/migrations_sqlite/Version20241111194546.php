<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241111194546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add has_car field';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_event_entries ADD COLUMN has_car BOOLEAN');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_event_entries AS SELECT user_id, event_id, present, transport, accomodation, date, comment FROM orm_event_entries');
        $this->addSql('DROP TABLE orm_event_entries');
        $this->addSql('CREATE TABLE orm_event_entries (user_id INTEGER NOT NULL, event_id INTEGER NOT NULL, present BOOLEAN NOT NULL, transport BOOLEAN NOT NULL, accomodation BOOLEAN NOT NULL, date DATETIME NOT NULL, comment VARCHAR(255) NOT NULL, PRIMARY KEY(user_id, event_id), CONSTRAINT FK_4C3A1770A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4C3A177071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_event_entries (user_id, event_id, present, transport, accomodation, date, comment) SELECT user_id, event_id, present, transport, accomodation, date, comment FROM __temp__orm_event_entries');
        $this->addSql('DROP TABLE __temp__orm_event_entries');
        $this->addSql('CREATE INDEX IDX_4C3A1770A76ED395 ON orm_event_entries (user_id)');
        $this->addSql('CREATE INDEX IDX_4C3A177071F7E88B ON orm_event_entries (event_id)');
    }
}
