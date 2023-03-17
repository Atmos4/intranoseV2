<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230317144700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_entries (user_id INT NOT NULL, event_id INT NOT NULL, present TINYINT(1) NOT NULL, transport TINYINT(1) NOT NULL, accomodation TINYINT(1) NOT NULL, date DATETIME NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_A8AF1A6AA76ED395 (user_id), INDEX IDX_A8AF1A6A71F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, deadline DATETIME NOT NULL, open TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race_entries (user_id INT NOT NULL, race_id INT NOT NULL, present TINYINT(1) NOT NULL, upgraded TINYINT(1) NOT NULL, licence INT NOT NULL, sport_ident INT NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_324866B5A76ED395 (user_id), INDEX IDX_324866B56E59D40D (race_id), PRIMARY KEY(user_id, race_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE races (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, name VARCHAR(255) NOT NULL, place VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, licence INT NOT NULL, sportident INT NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postal_code INT NOT NULL, city VARCHAR(255) NOT NULL, birthdate DATETIME NOT NULL, nose_email VARCHAR(255) NOT NULL, real_email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_entries ADD CONSTRAINT FK_A8AF1A6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE event_entries ADD CONSTRAINT FK_A8AF1A6A71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE race_entries ADD CONSTRAINT FK_324866B5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE race_entries ADD CONSTRAINT FK_324866B56E59D40D FOREIGN KEY (race_id) REFERENCES races (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_entries DROP FOREIGN KEY FK_A8AF1A6AA76ED395');
        $this->addSql('ALTER TABLE event_entries DROP FOREIGN KEY FK_A8AF1A6A71F7E88B');
        $this->addSql('ALTER TABLE race_entries DROP FOREIGN KEY FK_324866B5A76ED395');
        $this->addSql('ALTER TABLE race_entries DROP FOREIGN KEY FK_324866B56E59D40D');
        $this->addSql('DROP TABLE event_entries');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE race_entries');
        $this->addSql('DROP TABLE races');
        $this->addSql('DROP TABLE users');
    }
}
