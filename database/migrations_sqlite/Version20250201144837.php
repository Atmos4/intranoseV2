<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201144837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add club features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_club_features (club_slug VARCHAR(255) NOT NULL, featureName VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(featureName, club_slug), CONSTRAINT FK_58EE7BA0FC555182 FOREIGN KEY (club_slug) REFERENCES orm_clubs (slug) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_58EE7BA0FC555182 ON orm_club_features (club_slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_club_features');
    }
}
