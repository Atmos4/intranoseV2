<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125111322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_user_features (user_id INTEGER NOT NULL, featureName VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(featureName, user_id), CONSTRAINT FK_BBD83509A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BBD83509A76ED395 ON orm_user_features (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_user_features');
    }
}
