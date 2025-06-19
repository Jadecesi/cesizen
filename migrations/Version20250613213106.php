<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613213106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diagnostic_event (diagnostic_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_9005B15A224CCA91 (diagnostic_id), INDEX IDX_9005B15A71F7E88B (event_id), PRIMARY KEY(diagnostic_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE diagnostic_event ADD CONSTRAINT FK_9005B15A224CCA91 FOREIGN KEY (diagnostic_id) REFERENCES diagnostic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostic_event ADD CONSTRAINT FK_9005B15A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_event DROP FOREIGN KEY FK_D44771C471F7E88B');
        $this->addSql('ALTER TABLE reponse_event DROP FOREIGN KEY FK_D44771C4CF18BB82');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7605B523D');
        $this->addSql('DROP TABLE reponse_event');
        $this->addSql('DROP TABLE reponse');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse_event (reponse_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D44771C471F7E88B (event_id), INDEX IDX_D44771C4CF18BB82 (reponse_id), PRIMARY KEY(reponse_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, diagnostics_id INT NOT NULL, INDEX IDX_5FB6DEC7605B523D (diagnostics_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reponse_event ADD CONSTRAINT FK_D44771C471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_event ADD CONSTRAINT FK_D44771C4CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7605B523D FOREIGN KEY (diagnostics_id) REFERENCES diagnostic (id)');
        $this->addSql('ALTER TABLE diagnostic_event DROP FOREIGN KEY FK_9005B15A224CCA91');
        $this->addSql('ALTER TABLE diagnostic_event DROP FOREIGN KEY FK_9005B15A71F7E88B');
        $this->addSql('DROP TABLE diagnostic_event');
    }
}
