<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240410100720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change descriptions to text';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_activities CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE orm_events CHANGE description description LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_activities CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE orm_events CHANGE description description VARCHAR(255) NOT NULL');
    }
}
