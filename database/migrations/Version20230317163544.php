<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230317163544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE races ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE races ADD CONSTRAINT FK_5DBD1EC971F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('CREATE INDEX IDX_5DBD1EC971F7E88B ON races (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE races DROP FOREIGN KEY FK_5DBD1EC971F7E88B');
        $this->addSql('DROP INDEX IDX_5DBD1EC971F7E88B ON races');
        $this->addSql('ALTER TABLE races DROP event_id');
    }
}
