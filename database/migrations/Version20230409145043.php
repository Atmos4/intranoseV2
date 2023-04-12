<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409145043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Families';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE families (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users ADD family_id INT DEFAULT NULL, ADD family_leader TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9C35E566A FOREIGN KEY (family_id) REFERENCES families (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9C35E566A ON users (family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9C35E566A');
        $this->addSql('DROP TABLE families');
        $this->addSql('DROP INDEX IDX_1483A5E9C35E566A ON users');
        $this->addSql('ALTER TABLE users DROP family_id, DROP family_leader');
    }
}