<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240821184318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'wip sqlite - WARNING drop the db before you execute this';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_access_tokens (id VARCHAR(36) NOT NULL, user_id INTEGER DEFAULT NULL, hashed_validator VARCHAR(255) DEFAULT NULL, expiration DATETIME NOT NULL, type VARCHAR(20) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_BC4489A6A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BC4489A6A76ED395 ON orm_access_tokens (user_id)');
        $this->addSql('CREATE TABLE orm_activities (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, date DATETIME NOT NULL, deadline DATETIME NOT NULL, open BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, location_label VARCHAR(255) NOT NULL, location_url VARCHAR(255) NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_DA9A084071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_DA9A084071F7E88B ON orm_activities (event_id)');
        $this->addSql('CREATE TABLE orm_activity_entries (user_id INTEGER NOT NULL, activity_id INTEGER NOT NULL, category_id INTEGER DEFAULT NULL, present BOOLEAN NOT NULL, comment VARCHAR(255) NOT NULL, PRIMARY KEY(user_id, activity_id), CONSTRAINT FK_9D0FAE1A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9D0FAE181C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9D0FAE112469DE2 FOREIGN KEY (category_id) REFERENCES orm_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9D0FAE1A76ED395 ON orm_activity_entries (user_id)');
        $this->addSql('CREATE INDEX IDX_9D0FAE181C06096 ON orm_activity_entries (activity_id)');
        $this->addSql('CREATE INDEX IDX_9D0FAE112469DE2 ON orm_activity_entries (category_id)');
        $this->addSql('CREATE TABLE orm_categories (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, activity_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, removed BOOLEAN NOT NULL, CONSTRAINT FK_5598E1CD81C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5598E1CD81C06096 ON orm_categories (activity_id)');
        $this->addSql('CREATE TABLE orm_event_entries (user_id INTEGER NOT NULL, event_id INTEGER NOT NULL, present BOOLEAN NOT NULL, transport BOOLEAN NOT NULL, accomodation BOOLEAN NOT NULL, date DATETIME NOT NULL, comment VARCHAR(255) NOT NULL, PRIMARY KEY(user_id, event_id), CONSTRAINT FK_4C3A1770A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4C3A177071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4C3A1770A76ED395 ON orm_event_entries (user_id)');
        $this->addSql('CREATE INDEX IDX_4C3A177071F7E88B ON orm_event_entries (event_id)');
        $this->addSql('CREATE TABLE orm_events (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, description CLOB NOT NULL, open BOOLEAN NOT NULL, bulletin_url VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE orm_families (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE orm_notifications_subscriptions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, endpoint VARCHAR(255) NOT NULL, p256dh VARCHAR(255) NOT NULL, auth VARCHAR(255) NOT NULL, CONSTRAINT FK_14C0362BA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_14C0362BA76ED395 ON orm_notifications_subscriptions (user_id)');
        $this->addSql('CREATE TABLE orm_shared_documents (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, activity_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, date DATETIME NOT NULL, mime VARCHAR(255) NOT NULL, permission_level VARCHAR(255) NOT NULL, CONSTRAINT FK_24F818CF81C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_24F818CF71F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_24F818CF81C06096 ON orm_shared_documents (activity_id)');
        $this->addSql('CREATE INDEX IDX_24F818CF71F7E88B ON orm_shared_documents (event_id)');
        $this->addSql('CREATE TABLE orm_user_feedbacks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, description CLOB NOT NULL, CONSTRAINT FK_830ADE1AA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_830ADE1AA76ED395 ON orm_user_feedbacks (user_id)');
        $this->addSql('CREATE TABLE orm_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, family_id INTEGER DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, birthdate DATETIME NOT NULL, nose_email VARCHAR(255) NOT NULL, real_email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, family_leader BOOLEAN NOT NULL, CONSTRAINT FK_4EBE09C5C35E566A FOREIGN KEY (family_id) REFERENCES orm_families (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4EBE09C5C35E566A ON orm_users (family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_access_tokens');
        $this->addSql('DROP TABLE orm_activities');
        $this->addSql('DROP TABLE orm_activity_entries');
        $this->addSql('DROP TABLE orm_categories');
        $this->addSql('DROP TABLE orm_event_entries');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('DROP TABLE orm_families');
        $this->addSql('DROP TABLE orm_notifications_subscriptions');
        $this->addSql('DROP TABLE orm_shared_documents');
        $this->addSql('DROP TABLE orm_user_feedbacks');
        $this->addSql('DROP TABLE orm_users');
    }
}
