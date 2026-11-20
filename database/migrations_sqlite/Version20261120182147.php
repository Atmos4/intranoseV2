<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120182147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Many to many group conversations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_conversation_usergroup (conversation_id INTEGER NOT NULL, usergroup_id INTEGER NOT NULL, PRIMARY KEY(conversation_id, usergroup_id), CONSTRAINT FK_C5FDFBFD9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C5FDFBFDD2112630 FOREIGN KEY (usergroup_id) REFERENCES orm_user_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C5FDFBFD9AC0396 ON orm_conversation_usergroup (conversation_id)');
        $this->addSql('CREATE INDEX IDX_C5FDFBFDD2112630 ON orm_conversation_usergroup (usergroup_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN NOT NULL, is_accomodation BOOLEAN NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
        // keep old group -> conversation links (temp table: the join table's ON DELETE CASCADE could wipe rows when orm_user_groups is dropped)
        $this->addSql('CREATE TEMPORARY TABLE __group_conv AS SELECT conversation_id, id AS usergroup_id FROM orm_user_groups WHERE conversation_id IS NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_user_groups AS SELECT id, name, color FROM orm_user_groups');
        $this->addSql('DROP TABLE orm_user_groups');
        $this->addSql('CREATE TABLE orm_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO orm_user_groups (id, name, color) SELECT id, name, color FROM __temp__orm_user_groups');
        $this->addSql('DROP TABLE __temp__orm_user_groups');
        $this->addSql('INSERT INTO orm_conversation_usergroup (conversation_id, usergroup_id) SELECT conversation_id, usergroup_id FROM __group_conv');
        $this->addSql('DROP TABLE __group_conv');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_conversation_usergroup');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN DEFAULT 1 NOT NULL, is_accomodation BOOLEAN DEFAULT 1 NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_user_groups AS SELECT id, name, color FROM orm_user_groups');
        $this->addSql('DROP TABLE orm_user_groups');
        $this->addSql('CREATE TABLE orm_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, CONSTRAINT FK_33EC1E8D9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_user_groups (id, name, color) SELECT id, name, color FROM __temp__orm_user_groups');
        $this->addSql('DROP TABLE __temp__orm_user_groups');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33EC1E8D9AC0396 ON orm_user_groups (conversation_id)');
    }
}
