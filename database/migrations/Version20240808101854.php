<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808101854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_notifications_subscriptions CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_notifications_subscriptions ADD CONSTRAINT FK_14C0362BA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('CREATE INDEX IDX_14C0362BA76ED395 ON orm_notifications_subscriptions (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_notifications_subscriptions DROP FOREIGN KEY FK_14C0362BA76ED395');
        $this->addSql('DROP INDEX IDX_14C0362BA76ED395 ON orm_notifications_subscriptions');
        $this->addSql('ALTER TABLE orm_notifications_subscriptions CHANGE user_id user_id INT NOT NULL');
    }
}
