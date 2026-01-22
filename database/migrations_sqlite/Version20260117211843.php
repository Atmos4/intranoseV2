<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117211843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Groups messages';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_user_groups AS SELECT id, name, color FROM orm_user_groups');
        $this->addSql('DROP TABLE orm_user_groups');
        $this->addSql('CREATE TABLE orm_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, conversation_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, CONSTRAINT FK_33EC1E8D9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO orm_user_groups (id, name, color) SELECT id, name, color FROM __temp__orm_user_groups');
        $this->addSql('DROP TABLE __temp__orm_user_groups');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33EC1E8D9AC0396 ON orm_user_groups (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__orm_user_groups AS SELECT id, name, color FROM orm_user_groups');
        $this->addSql('DROP TABLE orm_user_groups');
        $this->addSql('CREATE TABLE orm_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO orm_user_groups (id, name, color) SELECT id, name, color FROM __temp__orm_user_groups');
        $this->addSql('DROP TABLE __temp__orm_user_groups');
    }
}
