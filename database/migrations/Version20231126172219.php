<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231126172219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'User feedback';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_user_feedbacks (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, description LONGTEXT NOT NULL, INDEX IDX_830ADE1AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orm_user_feedbacks ADD CONSTRAINT FK_830ADE1AA76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_user_feedbacks DROP FOREIGN KEY FK_830ADE1AA76ED395');
        $this->addSql('DROP TABLE orm_user_feedbacks');
    }
}
