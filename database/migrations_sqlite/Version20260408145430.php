<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408145430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_team_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, activity_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, published BOOLEAN NOT NULL, CONSTRAINT FK_20A5426971F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_20A5426981C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_20A5426971F7E88B ON orm_team_groups (event_id)');
        $this->addSql('CREATE INDEX IDX_20A5426981C06096 ON orm_team_groups (activity_id)');
        $this->addSql('CREATE TABLE orm_teams (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_group_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_CCFF8E7446160539 FOREIGN KEY (team_group_id) REFERENCES orm_team_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CCFF8E7446160539 ON orm_teams (team_group_id)');
        $this->addSql('CREATE TABLE orm_team_user (team_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(team_id, user_id), CONSTRAINT FK_87A266E4296CD8AE FOREIGN KEY (team_id) REFERENCES orm_teams (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_87A266E4A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_87A266E4296CD8AE ON orm_team_user (team_id)');
        $this->addSql('CREATE INDEX IDX_87A266E4A76ED395 ON orm_team_user (user_id)');
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
        $this->addSql('DROP TABLE orm_team_groups');
        $this->addSql('DROP TABLE orm_teams');
        $this->addSql('DROP TABLE orm_team_user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_events AS SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM orm_events');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL, is_transport BOOLEAN DEFAULT 1 NOT NULL, is_accomodation BOOLEAN DEFAULT 1 NOT NULL, type VARCHAR(255) DEFAULT \'COMPLEX\' NOT NULL, CONSTRAINT FK_610506059AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_events (id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type) SELECT id, conversation_id, name, start_date, end_date, deadline, description, open, bulletin_url, is_transport, is_accomodation, type FROM __temp__orm_events');
        $this->addSql('DROP TABLE __temp__orm_events');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_610506059AC0396 ON orm_events (conversation_id)');
    }
}
