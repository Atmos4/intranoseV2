<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709040642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_auth_clubs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE orm_auth_users (id VARCHAR(36) NOT NULL, login VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE orm_authuser_authclub (authuser_id VARCHAR(36) NOT NULL, authclub_id INTEGER NOT NULL, PRIMARY KEY(authuser_id, authclub_id), CONSTRAINT FK_BF8BE7CE7EFE93F4 FOREIGN KEY (authuser_id) REFERENCES orm_auth_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF8BE7CEB8894A53 FOREIGN KEY (authclub_id) REFERENCES orm_auth_clubs (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BF8BE7CE7EFE93F4 ON orm_authuser_authclub (authuser_id)');
        $this->addSql('CREATE INDEX IDX_BF8BE7CEB8894A53 ON orm_authuser_authclub (authclub_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_auth_clubs');
        $this->addSql('DROP TABLE orm_auth_users');
        $this->addSql('DROP TABLE orm_authuser_authclub');
    }
}
