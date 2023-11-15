<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115152705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove upgraded from RaceEntry';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_race_entries DROP upgraded');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_race_entries ADD upgraded TINYINT(1) NOT NULL');
    }
}
