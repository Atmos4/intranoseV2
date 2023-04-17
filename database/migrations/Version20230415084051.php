<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415084051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Improve users table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD active TINYINT(1) NOT NULL, DROP licence, DROP sportident, DROP address, DROP postal_code, DROP city');
        // set existing users to active
        $this->addSql('UPDATE users SET active=1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD licence INT NOT NULL, ADD sportident INT NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD postal_code INT NOT NULL, ADD city VARCHAR(255) NOT NULL, DROP active');
    }
}