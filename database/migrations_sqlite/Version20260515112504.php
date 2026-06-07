<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515112504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Guardians';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_guardians (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE orm_user_guardian (user_id INTEGER NOT NULL, guardian_id INTEGER NOT NULL, PRIMARY KEY(user_id, guardian_id), CONSTRAINT FK_6050894FA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6050894F11CC8B0A FOREIGN KEY (guardian_id) REFERENCES orm_guardians (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6050894FA76ED395 ON orm_user_guardian (user_id)');
        $this->addSql('CREATE INDEX IDX_6050894F11CC8B0A ON orm_user_guardian (guardian_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN NOT NULL, is_accomodation BOOLEAN NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_guardians');
        $this->addSql('DROP TABLE orm_user_guardian');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN DEFAULT 1 NOT NULL, is_accomodation BOOLEAN DEFAULT 1 NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
    }
}
