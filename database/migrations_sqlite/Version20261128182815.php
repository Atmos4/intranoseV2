<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261128182815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Event cancellation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN NOT NULL, is_accomodation BOOLEAN NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, is_cancelled BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN DEFAULT 1 NOT NULL, is_accomodation BOOLEAN DEFAULT 1 NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
    }
}
