<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240427121927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add open column to activities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_activities ADD open TINYINT(1) NOT NULL, CHANGE deadline deadline DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_activities DROP open, CHANGE deadline deadline DATETIME DEFAULT NULL');
    }
}
