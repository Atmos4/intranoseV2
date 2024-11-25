<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125145238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'messages';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_conversations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, private_hash VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26C716EB417A1606 ON orm_conversations (private_hash)');
        $this->addSql('CREATE TABLE orm_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER DEFAULT NULL, conversation_id INTEGER DEFAULT NULL, content VARCHAR(255) NOT NULL, sentAt DATETIME NOT NULL, CONSTRAINT FK_B08F1EBCF624B39D FOREIGN KEY (sender_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B08F1EBC9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B08F1EBCF624B39D ON orm_messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_B08F1EBC9AC0396 ON orm_messages (conversation_id)');
        $this->addSql('CREATE TABLE orm_user_conversations (user_id INTEGER NOT NULL, conversation_id INTEGER NOT NULL, joinedAt DATETIME NOT NULL, lastRead DATETIME NOT NULL, hasUnreadMessages BOOLEAN NOT NULL, directUser_id INTEGER DEFAULT NULL, PRIMARY KEY(user_id, conversation_id), CONSTRAINT FK_352CADACA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_352CADAC9AC0396 FOREIGN KEY (conversation_id) REFERENCES orm_conversations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_352CADAC5D0E93A9 FOREIGN KEY (directUser_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_352CADACA76ED395 ON orm_user_conversations (user_id)');
        $this->addSql('CREATE INDEX IDX_352CADAC9AC0396 ON orm_user_conversations (conversation_id)');
        $this->addSql('CREATE INDEX IDX_352CADAC5D0E93A9 ON orm_user_conversations (directUser_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_conversations');
        $this->addSql('DROP TABLE orm_messages');
        $this->addSql('DROP TABLE orm_user_conversations');
    }
}
