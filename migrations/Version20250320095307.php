<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320095307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO event (nom, stress) VALUES 
            ("Décès du conjoint", 100), 
            ("Divorce", 73), 
            ("Séparation", 65), 
            ("Séjour en prison", 63), 
            ("Décès d’un proche parent", 53), 
            ("Maladies ou blessures personnelles", 53), 
            ("Mariage", 60), 
            ("Perte d’emploi", 47), 
            ("Réconciliation avec le conjoint", 45), 
            ("Retraite", 45), 
            ("Modification de l’état de santé d’un membre de la famille", 44), 
            ("Grossesse", 40), 
            ("Difficultés sexuelles", 39), 
            ("Ajout d’un membre dans la famille", 39), 
            ("Changement dans la vie professionnelle", 39), 
            ("Modification de la situation financière", 38), 
            ("Mort d’un ami proche", 37), 
            ("Changement de carrière", 36), 
            ("Modification du nombre de disputes avec le conjoint", 35), 
            ("Hypothèque supérieure à un an de salaire", 31), 
            ("Saisie d’hypothèque ou de prêt", 30), 
            ("Modification de ses responsabilités professionnelles", 29), 
            ("Départ de l’un des enfants", 29), 
            ("Problème avec les beaux-parents", 29), 
            ("Succès personnel éclatant", 28), 
            ("Début ou fin d’emploi du conjoint", 26), 
            ("Première ou dernière année d’études", 26), 
            ("Modification de ses conditions de vie", 25), 
            ("Changements dans ses habitudes personnelles", 24), 
            ("Difficultés avec son patron", 23), 
            ("Modification des heures et des conditions de travail", 20), 
            ("Changement de domicile", 20), 
            ("Changement d’école", 20), 
            ("Changement du type ou de la quantité de loisirs", 19), 
            ("Modification des activités religieuses", 19), 
            ("Modification des activités sociales", 18), 
            ("Hypothèque ou prêt inférieur à un an de salaire", 17), 
            ("Modification des habitudes de sommeil", 16), 
            ("Modification du nombre de réunions familiales", 15), 
            ("Modification des habitudes alimentaires", 15), 
            ("Voyage ou vacances", 13), 
            ("Noël", 12), 
            ("Infractions mineures à la loi", 11)
        ;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
