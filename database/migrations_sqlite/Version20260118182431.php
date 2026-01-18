<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118182431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last connection to user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_users ADD COLUMN last_connection DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_users AS SELECT id, family_id, last_name, first_name, gender, login, password, permission, birthdate, nose_email, real_email, phone, picture, status, family_leader FROM orm_users');
        $this->addSql('DROP TABLE orm_users');
        $this->addSql('CREATE TABLE orm_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, family_id INTEGER DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, birthdate DATETIME NOT NULL, nose_email VARCHAR(255) NOT NULL, real_email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, family_leader BOOLEAN NOT NULL, CONSTRAINT FK_4EBE09C5C35E566A FOREIGN KEY (family_id) REFERENCES orm_families (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_users (id, family_id, last_name, first_name, gender, login, password, permission, birthdate, nose_email, real_email, phone, picture, status, family_leader) SELECT id, family_id, last_name, first_name, gender, login, password, permission, birthdate, nose_email, real_email, phone, picture, status, family_leader FROM __temp__orm_users');
        $this->addSql('DROP TABLE __temp__orm_users');
        $this->addSql('CREATE INDEX IDX_4EBE09C5C35E566A ON orm_users (family_id)');
    }
}
