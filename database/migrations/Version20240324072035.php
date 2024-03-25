<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324072035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'activities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_activities (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, date DATETIME NOT NULL, name VARCHAR(255) NOT NULL, place VARCHAR(255) NOT NULL, INDEX IDX_DA9A084071F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_activity_entries (user_id INT NOT NULL, activity_id INT NOT NULL, category_id INT DEFAULT NULL, present TINYINT(1) NOT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_9D0FAE1A76ED395 (user_id), INDEX IDX_9D0FAE181C06096 (activity_id), INDEX IDX_9D0FAE112469DE2 (category_id), PRIMARY KEY(user_id, activity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orm_activities ADD CONSTRAINT FK_DA9A084071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_activity_entries ADD CONSTRAINT FK_9D0FAE1A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_activity_entries ADD CONSTRAINT FK_9D0FAE181C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id)');
        $this->addSql('ALTER TABLE orm_activity_entries ADD CONSTRAINT FK_9D0FAE112469DE2 FOREIGN KEY (category_id) REFERENCES orm_categories (id)');
        $this->addSql('ALTER TABLE orm_categories ADD activity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_categories ADD CONSTRAINT FK_5598E1CD81C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id)');
        $this->addSql('CREATE INDEX IDX_5598E1CD81C06096 ON orm_categories (activity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_categories DROP FOREIGN KEY FK_5598E1CD81C06096');
        $this->addSql('ALTER TABLE orm_activities DROP FOREIGN KEY FK_DA9A084071F7E88B');
        $this->addSql('ALTER TABLE orm_activity_entries DROP FOREIGN KEY FK_9D0FAE1A76ED395');
        $this->addSql('ALTER TABLE orm_activity_entries DROP FOREIGN KEY FK_9D0FAE181C06096');
        $this->addSql('ALTER TABLE orm_activity_entries DROP FOREIGN KEY FK_9D0FAE112469DE2');
        $this->addSql('DROP TABLE orm_activities');
        $this->addSql('DROP TABLE orm_activity_entries');
        $this->addSql('DROP INDEX IDX_5598E1CD81C06096 ON orm_categories');
        $this->addSql('ALTER TABLE orm_categories DROP activity_id');
    }
}
