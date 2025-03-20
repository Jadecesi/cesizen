<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320094515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse_event (reponse_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D44771C4CF18BB82 (reponse_id), INDEX IDX_D44771C471F7E88B (event_id), PRIMARY KEY(reponse_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reponse_event ADD CONSTRAINT FK_D44771C4CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_event ADD CONSTRAINT FK_D44771C471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse_event DROP FOREIGN KEY FK_D44771C4CF18BB82');
        $this->addSql('ALTER TABLE reponse_event DROP FOREIGN KEY FK_D44771C471F7E88B');
        $this->addSql('DROP TABLE reponse_event');
    }
}
