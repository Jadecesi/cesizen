<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331113258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contenu ADD utilisateur_id INT NOT NULL, ADD date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE contenu ADD CONSTRAINT FK_89C2003FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_89C2003FFB88E14F ON contenu (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contenu DROP FOREIGN KEY FK_89C2003FFB88E14F');
        $this->addSql('DROP INDEX IDX_89C2003FFB88E14F ON contenu');
        $this->addSql('ALTER TABLE contenu DROP utilisateur_id, DROP date_creation');
    }
}
