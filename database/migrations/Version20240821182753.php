<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240821182753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove all race stuff';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orm_categories DROP FOREIGN KEY FK_5598E1CD6E59D40D');
        $this->addSql('ALTER TABLE orm_shared_documents DROP FOREIGN KEY FK_24F818CF6E59D40D');
        $this->addSql('ALTER TABLE orm_races DROP FOREIGN KEY FK_780B2E571F7E88B');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A773912469DE2');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A77396E59D40D');
        $this->addSql('ALTER TABLE orm_race_entries DROP FOREIGN KEY FK_A98A7739A76ED395');
        $this->addSql('DROP TABLE orm_races');
        $this->addSql('DROP TABLE orm_race_entries');
        $this->addSql('DROP INDEX IDX_5598E1CD6E59D40D ON orm_categories');
        $this->addSql('ALTER TABLE orm_categories DROP race_id');
        $this->addSql('DROP INDEX IDX_24F818CF6E59D40D ON orm_shared_documents');
        $this->addSql('ALTER TABLE orm_shared_documents CHANGE race_id activity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_shared_documents ADD CONSTRAINT FK_24F818CF81C06096 FOREIGN KEY (activity_id) REFERENCES orm_activities (id)');
        $this->addSql('CREATE INDEX IDX_24F818CF81C06096 ON orm_shared_documents (activity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orm_races (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, date DATETIME NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, place VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_780B2E571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE orm_race_entries (user_id INT NOT NULL, race_id INT NOT NULL, category_id INT DEFAULT NULL, present TINYINT(1) NOT NULL, comment VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A98A77396E59D40D (race_id), INDEX IDX_A98A773912469DE2 (category_id), INDEX IDX_A98A7739A76ED395 (user_id), PRIMARY KEY(user_id, race_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE orm_races ADD CONSTRAINT FK_780B2E571F7E88B FOREIGN KEY (event_id) REFERENCES orm_events (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A773912469DE2 FOREIGN KEY (category_id) REFERENCES orm_categories (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A77396E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('ALTER TABLE orm_race_entries ADD CONSTRAINT FK_A98A7739A76ED395 FOREIGN KEY (user_id) REFERENCES orm_users (id)');
        $this->addSql('ALTER TABLE orm_categories ADD race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_categories ADD CONSTRAINT FK_5598E1CD6E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('CREATE INDEX IDX_5598E1CD6E59D40D ON orm_categories (race_id)');
        $this->addSql('ALTER TABLE orm_shared_documents DROP FOREIGN KEY FK_24F818CF81C06096');
        $this->addSql('DROP INDEX IDX_24F818CF81C06096 ON orm_shared_documents');
        $this->addSql('ALTER TABLE orm_shared_documents CHANGE activity_id race_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orm_shared_documents ADD CONSTRAINT FK_24F818CF6E59D40D FOREIGN KEY (race_id) REFERENCES orm_races (id)');
        $this->addSql('CREATE INDEX IDX_24F818CF6E59D40D ON orm_shared_documents (race_id)');
    }
}
