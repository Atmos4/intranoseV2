<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230423135447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Access tokens';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_tokens (id VARCHAR(36) NOT NULL, user_id INT DEFAULT NULL, expiration DATETIME NOT NULL, type VARCHAR(20) NOT NULL, INDEX IDX_58D184BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE access_tokens ADD CONSTRAINT FK_58D184BCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_tokens DROP FOREIGN KEY FK_58D184BCA76ED395');
        $this->addSql('DROP TABLE access_tokens');
    }
}