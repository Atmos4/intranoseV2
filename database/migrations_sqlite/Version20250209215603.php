<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209215603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add groups';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_event_usergroup (event_id INTEGER NOT NULL, usergroup_id INTEGER NOT NULL, PRIMARY KEY(event_id, usergroup_id), CONSTRAINT FK_26D24D971F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_26D24D9D2112630 FOREIGN KEY (usergroup_id) REFERENCES orm_user_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_26D24D971F7E88B ON orm_event_usergroup (event_id)');
        $this->addSql('CREATE INDEX IDX_26D24D9D2112630 ON orm_event_usergroup (usergroup_id)');
        $this->addSql('CREATE TABLE orm_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE orm_user_usergroup (user_id INTEGER NOT NULL, usergroup_id INTEGER NOT NULL, PRIMARY KEY(user_id, usergroup_id), CONSTRAINT FK_B7029984A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B7029984D2112630 FOREIGN KEY (usergroup_id) REFERENCES orm_user_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B7029984A76ED395 ON orm_user_usergroup (user_id)');
        $this->addSql('CREATE INDEX IDX_B7029984D2112630 ON orm_user_usergroup (usergroup_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_event_usergroup');
        $this->addSql('DROP TABLE orm_user_groups');
        $this->addSql('DROP TABLE orm_user_usergroup');
    }
}
