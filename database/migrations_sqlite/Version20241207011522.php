<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241207011522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicles';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_vehicles (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, manager_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, start_location VARCHAR(255) NOT NULL, return_location VARCHAR(255) NOT NULL, capacity VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, return_date DATETIME NOT NULL, CONSTRAINT FK_744369D0783E3463 FOREIGN KEY (manager_id) REFERENCES orm_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_744369D071F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_744369D0783E3463 ON orm_vehicles (manager_id)');
        $this->addSql('CREATE INDEX IDX_744369D071F7E88B ON orm_vehicles (event_id)');
        $this->addSql('CREATE TABLE orm_vehicle_user (vehicle_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(vehicle_id, user_id), CONSTRAINT FK_64CCF499545317D1 FOREIGN KEY (vehicle_id) REFERENCES orm_vehicles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_64CCF499A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_64CCF499545317D1 ON orm_vehicle_user (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_64CCF499A76ED395 ON orm_vehicle_user (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orm_vehicles');
        $this->addSql('DROP TABLE orm_vehicle_user');
    }
}
