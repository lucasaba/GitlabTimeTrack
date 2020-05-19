<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200519222516 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE milestone (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, gitlab_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, due_at DATETIME DEFAULT NULL, start_at DATETIME DEFAULT NULL, INDEX IDX_4FAC8382166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE milestone ADD CONSTRAINT FK_4FAC8382166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');

        $this->addSql('ALTER TABLE issue ADD milestone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E4B3E2EDA FOREIGN KEY (milestone_id) REFERENCES milestone (id)');
        $this->addSql('CREATE INDEX IDX_12AD233E4B3E2EDA ON issue (milestone_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE milestone');
    }
}
