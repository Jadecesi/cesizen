<?php
namespace App\DataFixtures;

use App\Entity\Contenu;
use App\Entity\Diagnostic;
use App\Entity\Event;
use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer les rôles
        $roleAdmin = new Role();
        $roleAdmin->setNom("ROLE_ADMIN");
        $manager->persist($roleAdmin);

        $roleUser = new Role();
        $roleUser->setNom("ROLE_USER");
        $manager->persist($roleUser);

        // Créer un utilisateur admin
        $userAdmin = new Utilisateur();
        $userAdmin->setEmail('admin@example.com');
        $userAdmin->setRole($roleAdmin);
        $userAdmin->setNom('admin');
        $userAdmin->setPrenom('admin');
        $userAdmin->setIsActif(true);
        $userAdmin->setPhotoProfile('profilePicture2.png');
        $userAdmin->setDateNaissance(new \DateTime('1990-01-01'));

        $hashedPasswordAdmin = $this->passwordHasher->hashPassword($userAdmin, 'adminpass');
        $userAdmin->setPassword($hashedPasswordAdmin);
        $manager->persist($userAdmin);

        $user = new Utilisateur();
        $user->setEmail('user@example.com');
        $user->setRole($roleUser);
        $user->setNom('user');
        $user->setPrenom('user');
        $user->setIsActif(true);
        $user->setPhotoProfile('profilePicture1.png');
        $user->setDateNaissance(new \DateTime('1999-01-01'));

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'userpass');
        $user->setPassword($hashedPassword);
        $manager->persist($user);


        $contenus = [];

        $contenus[] = (new Contenu())
            ->setTitre('La santé mentale, Grande cause nationale en 2025')
            ->setUrl('https://www.info.gouv.fr/actualite/la-sante-mentale-grande-cause-nationale-en-2025')
            ->setImage('https://www.info.gouv.fr/upload/media/content/0001/12/9ee5ae83f135af937dae1af0863f880f9ab7655d.jpg')
            ->setDescription('Le gouvernement français a désigné la santé mentale comme priorité nationale pour l\'année 2025. Cette initiative vise à sensibiliser le public et à renforcer les actions en faveur du bien-être psychologique.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('Santé mentale : définition et facteurs en jeu')
            ->setUrl('https://www.ameli.fr/assure/sante/themes/sante-mentale-de-l-adulte/sante-mentale-definition-et-facteurs-en-jeu')
            ->setImage('https://csnb.ca/sites/default/files/cover-images/resumegraph-facteurs%20de%20protection.png')
            ->setDescription('Cet article explique que la santé mentale est un état de bien-être indispensable pour se sentir en bonne santé. Il détaille les facteurs influençant la santé mentale, tels que le milieu de vie, les facteurs socio-économiques, l’histoire personnelle et relationnelle, les facteurs biologiques et l’état de santé.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('Activité physique et bien-être mental')
            ->setUrl('https://sante.gouv.fr/actualites/actualites-du-ministere/article/activite-physique-et-sportive-bouger-pour-une-bonne-sante-mentale')
            ->setImage('https://www.conflans-sainte-honorine.fr/wp-content/uploads/2024/09/web-sante-mentale-1500x1000-1.jpg')
            ->setDescription('Cet article met en avant les bienfaits de l’activité physique sur la santé mentale, notamment pour lutter contre le stress, améliorer la confiance en soi, prévenir la dépression et l’anxiété.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('Prendre soin de la santé mentale des 18-25 ans')
            ->setUrl('https://sante.gouv.fr/actualites/actualites-du-ministere/article/prendre-soin-de-la-sante-mentale-des-18-25-ans')
            ->setImage('https://www.jeunes.gouv.fr/sites/default/files/styles/page_header/public/2025-02/sante-mentale-fev-2025-png-4538.png')
            ->setDescription('Cet article aborde les défis spécifiques rencontrés par les jeunes adultes en matière de santé mentale et propose des pistes pour mieux les accompagner.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('La santé mentale, Grande Cause nationale 2025')
            ->setUrl('https://solidarites.gouv.fr/la-sante-mentale-grande-cause-nationale-2025')
            ->setImage('https://travail-emploi.gouv.fr/sites/travail-emploi/files/styles/w_1200/public/2025-03/Parlons-sant%C3%A9-mentale-grande-cause-nationale.jpg.webp')
            ->setDescription('Présentation des objectifs de la Grande Cause nationale 2025 dédiée à la santé mentale, pour lutter contre la stigmatisation et améliorer l’accès aux soins.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('L’information sur la santé mentale')
            ->setUrl('https://sante.gouv.fr/prevention-en-sante/sante-mentale/promotion-et-prevention/article/l-information-sur-la-sante-mentale')
            ->setImage('https://www.saint-ouen.fr/fileadmin/user_upload/2022-160-Sante-mentale-site.jpg')
            ->setDescription('Cet article souligne l’importance de la prévention en santé mentale, notamment à travers la promotion du bien-être, le repérage précoce et la prévention du suicide.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $contenus[] = (new Contenu())
            ->setTitre('Santé mentale des jeunes : conseils pour mieux vivre au quotidien')
            ->setUrl('https://www.santepubliquefrance.fr/presse/2023/sante-mentale-des-jeunes-des-conseils-pour-prendre-soin-de-sa-sante-mentale')
            ->setImage('https://cos-njord-dgefp-1j1s-prod.storage-eb4.cegedim.cloud/strapi-media/aides_sante_mentale_jeunes_e56064c783.jpg')
            ->setDescription('Des conseils pratiques pour aider les jeunes à préserver leur santé mentale et à mieux gérer le stress, l’anxiété ou la dépression.')
            ->setDateCreation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        foreach ($contenus as $contenu) {
            $contenu->setUtilisateur($userAdmin);
            $manager->persist($contenu);
        }

        // Création des catégories d'événements
        $categoriesData = [
            1 => 'Vie familiale',
            2 => 'Santé',
            3 => 'Travail',
            4 => 'Finances',
            5 => 'Habitudes de vie',
            6 => 'Social',
            7 => 'Justice',
        ];
        $categories = [];
        foreach ($categoriesData as $id => $libelle) {
            $cat = new \App\Entity\CategorieEvent();
            $cat->setLibelle($libelle);
            $manager->persist($cat);
            $categories[$id] = $cat;
        }

        // Création des événements avec leur catégorie
        $eventsList = [
            [1, 'Décès du conjoint', 100, 1],
            [2, 'Divorce', 73, 1],
            [3, 'Séparation', 65, 1],
            [4, 'Séjour en prison', 63, 7],
            [5, 'Décès d’un proche parent', 53, 1],
            [6, 'Maladies ou blessures personnelles', 53, 2],
            [7, 'Mariage', 60, 1],
            [8, 'Perte d’emploi', 47, 3],
            [9, 'Réconciliation avec le conjoint', 45, 1],
            [10, 'Retraite', 45, 3],
            [11, 'Modification de l’état de santé d’un membre de la famille', 44, 2],
            [12, 'Grossesse', 40, 1],
            [13, 'Difficultés sexuelles', 39, 2],
            [14, 'Ajout d’un membre dans la famille', 39, 1],
            [15, 'Changement dans la vie professionnelle', 39, 3],
            [16, 'Modification de la situation financière', 38, 4],
            [17, 'Mort d’un ami proche', 37, 6],
            [18, 'Changement de carrière', 36, 3],
            [19, 'Modification du nombre de disputes avec le conjoint', 35, 1],
            [20, 'Hypothèque supérieure à un an de salaire', 31, 4],
            [21, 'Saisie d’hypothèque ou de prêt', 30, 4],
            [22, 'Modification de ses responsabilités professionnelles', 29, 3],
            [23, 'Départ de l’un des enfants', 29, 1],
            [24, 'Problème avec les beaux-parents', 29, 1],
            [25, 'Succès personnel éclatant', 28, 3],
            [26, 'Début ou fin d’emploi du conjoint', 26, 1],
            [27, 'Première ou dernière année d’études', 26, 3],
            [28, 'Modification de ses conditions de vie', 25, 5],
            [29, 'Changements dans ses habitudes personnelles', 24, 5],
            [30, 'Difficultés avec son patron', 23, 3],
            [31, 'Modification des heures et des conditions de travail', 20, 3],
            [32, 'Changement de domicile', 20, 5],
            [33, 'Changement d’école', 20, 3],
            [34, 'Changement du type ou de la quantité de loisirs', 19, 5],
            [35, 'Modification des activités religieuses', 19, 6],
            [36, 'Modification des activités sociales', 18, 6],
            [37, 'Hypothèque ou prêt inférieur à un an de salaire', 17, 4],
            [38, 'Modification des habitudes de sommeil', 16, 2],
            [39, 'Modification du nombre de réunions familiales', 15, 1],
            [40, 'Modification des habitudes alimentaires', 15, 2],
            [41, 'Voyage ou vacances', 13, 5],
            [42, 'Noël', 12, 6],
            [43, 'Infractions mineures à la loi', 11, 7],
        ];
        $eventsById = [];
        foreach ($eventsList as [$id, $nom, $stress, $catId]) {
            $event = new \App\Entity\Event();
            $event->setNom($nom);
            $event->setStress($stress);
            $event->setCategorie($categories[$catId]);
            $manager->persist($event);
            $eventsById[$id] = $event;
        }

        $manager->flush();

        // Diagnostic élevé
        $eventNamesEleve = [
            'Décès du conjoint',
            'Divorce',
            'Séjour en prison',
            'Maladies ou blessures personnelles',
            'Mariage'
        ];
        $eventsEleve = $this->getEventsByNames($manager, $eventNamesEleve);
        $diagnosticEleve = new Diagnostic();
        $diagnosticEleve->setUtilisateur($user);
        $diagnosticEleve->setTotalStress(array_sum(array_map(fn($e) => $e->getStress(), $eventsEleve)));
        foreach ($eventsEleve as $event) {
            $diagnosticEleve->addEvent($event);
        }
        $manager->persist($diagnosticEleve);

        // Diagnostic moyen
        $eventNamesMoyen = [
            'Perte d’emploi',
            'Retraite',
            'Modification de la situation financière',
            'Changement de carrière',
            'Modification de ses responsabilités professionnelles'
        ];
        $eventsMoyen = $this->getEventsByNames($manager, $eventNamesMoyen);
        $diagnosticMoyen = new Diagnostic();
        $diagnosticMoyen->setUtilisateur($user);
        $diagnosticMoyen->setTotalStress(array_sum(array_map(fn($e) => $e->getStress(), $eventsMoyen)));
        foreach ($eventsMoyen as $event) {
            $diagnosticMoyen->addEvent($event);
        }
        $manager->persist($diagnosticMoyen);

        $eventNamesFaible = [
            'Modification des habitudes alimentaires',
            'Voyage ou vacances',
            'Noël',
            'Changement de domicile',
            'Modification des activités sociales',
            'Modification des habitudes de sommeil'
        ];
        $eventsFaible = $this->getEventsByNames($manager, $eventNamesFaible);
        $diagnosticFaible = new Diagnostic();
        $diagnosticFaible->setUtilisateur($user);
        $diagnosticFaible->setTotalStress(array_sum(array_map(fn($e) => $e->getStress(), $eventsFaible)));
        foreach ($eventsFaible as $event) {
            $diagnosticFaible->addEvent($event);
        }
        $manager->persist($diagnosticFaible);

        $manager->flush();
    }

    private function getEventsByNames(ObjectManager $manager, array $noms): array
    {
        $repo = $manager->getRepository(Event::class);
        $results = [];

        foreach ($noms as $nom) {
            $event = $repo->findOneBy(['nom' => $nom]);
            if ($event) {
                $results[] = $event;
            }
        }

        return $results;
    }
}

