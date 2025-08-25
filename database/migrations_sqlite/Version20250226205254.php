<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226205254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link conversation to event';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, google_calendar_id VARCHAR(255) DEFAULT NULL, google_calendar_url VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type) SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_messages AS SELECT id, sender_id, conversation_id, content, sentAt FROM orm_messages');
        $this->addSql('DROP TABLE orm_messages');
        $this->addSql('CREATE TABLE orm_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER DEFAULT NULL, conversation_id INTEGER DEFAULT NULL, content CLOB NOT NULL, sentAt DATETIME NOT NULL, CONSTRAINT FK_B08F1EBCF624B39D FOREIGN KEY (sender_id) REFERENCES orm_users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B08F1EBC9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_messages (id, sender_id, conversation_id, content, sentAt) SELECT id, sender_id, conversation_id, content, sentAt FROM __temp__orm_messages');
        $this->addSql('DROP TABLE __temp__orm_messages');
        $this->addSql('CREATE INDEX IDX_B08F1EBC9AC0396 ON orm_messages (conversation_id)');
        $this->addSql('CREATE INDEX IDX_B08F1EBCF624B39D ON orm_messages (sender_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, google_calendar_id VARCHAR(255) DEFAULT NULL, google_calendar_url VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL)');
        $this->addSql('INSERT INTO orm_events (id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type) SELECT id, name, start_date, end_date, deadline, description, open, bulletin_url, google_calendar_id, google_calendar_url, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_messages AS SELECT id, sender_id, conversation_id, content, sentAt FROM orm_messages');
        $this->addSql('DROP TABLE orm_messages');
        $this->addSql('CREATE TABLE orm_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER DEFAULT NULL, conversation_id INTEGER DEFAULT NULL, content VARCHAR(255) NOT NULL, sentAt DATETIME NOT NULL, CONSTRAINT FK_B08F1EBCF624B39D FOREIGN KEY (sender_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B08F1EBC9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_messages (id, sender_id, conversation_id, content, sentAt) SELECT id, sender_id, conversation_id, content, sentAt FROM __temp__orm_messages');
        $this->addSql('DROP TABLE __temp__orm_messages');
        $this->addSql('CREATE INDEX IDX_B08F1EBCF624B39D ON orm_messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_B08F1EBC9AC0396 ON orm_messages (conversation_id)');
    }
}
