<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231016152816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'squash-10-2023';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_access_tokens (id VARCHAR(36) NOT NULL, user_id INT DEFAULT NULL, expiration DATETIME NOT NULL, type VARCHAR(20) NOT NULL, INDEX IDX_BC4489A6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_categories (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, removed TINYINT(1) NOT NULL, INDEX IDX_5598E1CD6E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_event_entries (user_id INT NOT NULL, event_id INT NOT NULL, present TINYINT(1) NOT NULL, transport TINYINT(1) NOT NULL, accomodation TINYINT(1) NOT NULL, date DATETIME NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_4C3A1770A76ED395 (user_id), INDEX IDX_4C3A177071F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_events (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, open TINYINT(1) NOT NULL, bulletin_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_families (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_race_entries (user_id INT NOT NULL, race_id INT NOT NULL, category_id INT DEFAULT NULL, present TINYINT(1) NOT NULL, upgraded TINYINT(1) NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_A98A7739A76ED395 (user_id), INDEX IDX_A98A77396E59D40D (race_id), INDEX IDX_A98A773912469DE2 (category_id), PRIMARY KEY(user_id, race_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_races (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, date DATETIME NOT NULL, name VARCHAR(255) NOT NULL, place VARCHAR(255) NOT NULL, INDEX IDX_780B2E571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_shared_documents (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, event_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, date DATETIME NOT NULL, mime VARCHAR(255) NOT NULL, is_public TINYINT(1) NOT NULL, INDEX IDX_24F818CF6E59D40D (race_id), INDEX IDX_24F818CF71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_users (id INT AUTO_INCREMENT NOT NULL, family_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, birthdate DATETIME NOT NULL, nose_email VARCHAR(255) NOT NULL, real_email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, family_leader TINYINT(1) NOT NULL, INDEX IDX_4EBE09C5C35E566A (family_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orm_access_tokens ADD CONSTRAINT FK_BC4489A6A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_categories ADD CONSTRAINT FK_5598E1CD6E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('ALTER TABLE orm_event_entries ADD CONSTRAINT FK_4C3A1770A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_event_entries ADD CONSTRAINT FK_4C3A177071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A7739A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A77396E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A773912469DE2 FOREIGN KEY (category_id) REFERENCES orm_categories (id)');
        $this->addSql('ALTER TABLE orm_races ADD CONSTRAINT FK_780B2E571F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_shared_documents ADD CONSTRAINT FK_24F818CF6E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('ALTER TABLE orm_shared_documents ADD CONSTRAINT FK_24F818CF71F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_users ADD CONSTRAINT FK_4EBE09C5C35E566A FOREIGN KEY (family_id) REFERENCES orm_families (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_access_tokens DROP FOREIGN KEY FK_BC4489A6A76ED395');
        $this->addSql('ALTER TABLE orm_categories DROP FOREIGN KEY FK_5598E1CD6E59D40D');
        $this->addSql('ALTER TABLE orm_event_entries DROP FOREIGN KEY FK_4C3A1770A76ED395');
        $this->addSql('ALTER TABLE orm_event_entries DROP FOREIGN KEY FK_4C3A177071F7E88B');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A7739A76ED395');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A77396E59D40D');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A773912469DE2');
        $this->addSql('ALTER TABLE orm_races DROP FOREIGN KEY FK_780B2E571F7E88B');
        $this->addSql('ALTER TABLE orm_shared_documents DROP FOREIGN KEY FK_24F818CF6E59D40D');
        $this->addSql('ALTER TABLE orm_shared_documents DROP FOREIGN KEY FK_24F818CF71F7E88B');
        $this->addSql('ALTER TABLE orm_users DROP FOREIGN KEY FK_4EBE09C5C35E566A');
        $this->addSql('DROP TABLE orm_access_tokens');
        $this->addSql('DROP TABLE orm_categories');
        $this->addSql('DROP TABLE orm_event_entries');
        $this->addSql('DROP TABLE orm_events');
        $this->addSql('DROP TABLE orm_families');
        $this->addSql('DROP TABLE orm_race_entries');
        $this->addSql('DROP TABLE orm_races');
        $this->addSql('DROP TABLE orm_shared_documents');
        $this->addSql('DROP TABLE orm_users');
    }
}