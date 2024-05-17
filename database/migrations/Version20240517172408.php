<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517172408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make hashed_validator nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_access_tokens CHANGE hashed_validator hashed_validator VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_access_tokens CHANGE hashed_validator hashed_validator VARCHAR(255) NOT NULL');
    }
}
