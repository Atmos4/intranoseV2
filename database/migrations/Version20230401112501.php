<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230401112501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a shared_documents table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shared_documents (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, event_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, date DATETIME NOT NULL, mime VARCHAR(255) NOT NULL, is_public TINYINT(1) NOT NULL, INDEX IDX_82270B6E59D40D (race_id), INDEX IDX_82270B71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shared_documents ADD CONSTRAINT FK_82270B6E59D40D FOREIGN KEY (race_id) REFERENCES races (id)');
        $this->addSql('ALTER TABLE shared_documents ADD CONSTRAINT FK_82270B71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shared_documents DROP FOREIGN KEY FK_82270B6E59D40D');
        $this->addSql('ALTER TABLE shared_documents DROP FOREIGN KEY FK_82270B71F7E88B');
        $this->addSql('DROP TABLE shared_documents');
    }
}