<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327154141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_3AF346686E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF346686E59D40D FOREIGN KEY (race_id) REFERENCES races (id)');
        $this->addSql('ALTER TABLE race_entries ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE race_entries ADD CONSTRAINT FK_324866B512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_324866B512469DE2 ON race_entries (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE race_entries DROP FOREIGN KEY FK_324866B512469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF346686E59D40D');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP INDEX IDX_324866B512469DE2 ON race_entries');
        $this->addSql('ALTER TABLE race_entries DROP category_id');
    }
}
