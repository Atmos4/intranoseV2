<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241007062815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicles';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_vehicles (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, event_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, start_location VARCHAR(255) NOT NULL, return_location VARCHAR(255) NOT NULL, capacity VARCHAR(255) NOT NULL, INDEX IDX_744369D0783E3463 (manager_id), INDEX IDX_744369D071F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orm_vehicle_user (vehicle_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_64CCF499545317D1 (vehicle_id), INDEX IDX_64CCF499A76ED395 (user_id), PRIMARY KEY(vehicle_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orm_vehicles ADD CONSTRAINT FK_744369D0783E3463 FOREIGN KEY (manager_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_vehicles ADD CONSTRAINT FK_744369D071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_vehicle_user ADD CONSTRAINT FK_64CCF499545317D1 FOREIGN KEY (vehicle_id) REFERENCES orm_vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orm_vehicle_user ADD CONSTRAINT FK_64CCF499A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_vehicles DROP FOREIGN KEY FK_744369D0783E3463');
        $this->addSql('ALTER TABLE orm_vehicles DROP FOREIGN KEY FK_744369D071F7E88B');
        $this->addSql('ALTER TABLE orm_vehicle_user DROP FOREIGN KEY FK_64CCF499545317D1');
        $this->addSql('ALTER TABLE orm_vehicle_user DROP FOREIGN KEY FK_64CCF499A76ED395');
        $this->addSql('DROP TABLE orm_vehicles');
        $this->addSql('DROP TABLE orm_vehicle_user');
    }
}
