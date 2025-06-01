<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\ResetPassword;
use App\Entity\Utilisateur;
use App\Form\EventType;
use App\Form\UtilisateurType;
use App\Repository\DiagnosticRepository;
use App\Repository\EventRepository;
use App\Repository\UtilisateurRepository;
use App\Service\ServiceMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct
    (
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }


    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(UtilisateurRepository $utilisateurRepository, DiagnosticRepository $diagnosticRepository, EventRepository $eventRepository)
    {
        $user = $this->getUser();
        $utilisateur = $utilisateurRepository->findAll();

        $diagnostics = $diagnosticRepository->findAll();

        $diagnosticsAdmin = $diagnosticRepository->findBy(['utilisateur' => $user]);

        $events = $eventRepository->findAll();

        $stressLevelStats = $this->getChartStressLevel($diagnostics);
        $evolutionStressByAge = $this->getChartEvolutionMoyenneStressByAge($diagnostics);
        $eventsByCategorie = $this->getChartEventsByCategorie($diagnostics);
        $eventsByCategorieAndAge = $this->getChartEventsByCategorieAndAge($diagnostics);

        $chartDataStressLevel = [
            'stressLevels' => $stressLevelStats['labels'],
            'stressData' => $stressLevelStats['data']
        ];

        $chartDataEvolutionByAge = [
            'age' => $evolutionStressByAge['age'],
            'data' => $evolutionStressByAge['data']
        ];

        $chartDataEventsByCategorie = [
            'categories' => $eventsByCategorie['labels'],
            'data' => $eventsByCategorie['data']
        ];

        // Données pour le nouveau graphique
        $chartDataEventsByCategorieAndAge = [
            'ages' => $eventsByCategorieAndAge['ages'],
            'datasets' => $eventsByCategorieAndAge['datasets'],
            'categories' => $eventsByCategorieAndAge['categories']
        ];

        return $this->render('Admin/dashboard.html.twig', [
            'utilisateurs' => $utilisateur,
            'chartDataStressLevel' => $chartDataStressLevel,
            'chartDataEvolutionByAge' => $chartDataEvolutionByAge,
            'eventsByCategorie' => $chartDataEventsByCategorie,
            'eventsByCategorieAndAge' => $chartDataEventsByCategorieAndAge,  // Nouvelle variable
            'events' => $events,
            'diagnosticAdmin' => $diagnosticsAdmin
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_edit_user', methods: ['GET', 'POST'])]
    public function editUser(Utilisateur $utilisateur, Request $request): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($utilisateur);
            $this->em->flush();
        }

        return $this->render('Admin/Modal/UserEdit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'action' => $this->generateUrl('app_edit_user', ['id' => $utilisateur->getId()]),
        ]);
    }

    #[Route('/user/{id}/status', name: 'app_modifie_status_user')]
    public function modificationStatusUser(Utilisateur $utilisateur): Response
    {
        dump($utilisateur->isActif());
        $newStatus = !$utilisateur->isActif();
        dump($newStatus);
        $utilisateur->setIsActif($newStatus);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $action = $newStatus ? 'Activation' : 'Désactivation';

        return $this->render('succesModal.html.twig', [
            'titre' => $action . ' réussie',
            'btn' => 'Retour au tableau de bord',
            'action' => $this->generateUrl('app_admin_dashboard'),
        ]);
    }

    #[Route('/user/{id}/confirm-status', name: 'app_confirm_satus_user')]
    public function confirmStatusUser(Utilisateur $utilisateur)
    {
        $action = $utilisateur->isActif() ? 'désactiver' : 'activer';
        return $this->render('Admin/Modal/confirm-status-user.html.twig', [
            'user' => $utilisateur,
            'action' => $action
        ]);
    }

    private function getChartStressLevel(array $diagnostics): array
    {
        foreach ($diagnostics as $diagnostic) {
            $totalStress = $diagnostic->getTotalStress();
            $allStress[] = [
                $diagnostic->getNiveauStress($totalStress) => $totalStress
            ];
        }

        $stats = [];
        foreach ($allStress as $stress) {
            $level = array_key_first($stress);
            $stats[$level] = ($stats[$level] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($stats),
            'data' => array_values($stats)
        ];
    }

    public function getChartEvolutionMoyenneStressByAge(array $diagnostics): array
    {
        $sommeByAge = [];
        $nbByAge = [];

        foreach ($diagnostics as $diagnostic) {
            $totalStress = $diagnostic->getTotalStress();
            $age = $diagnostic->getUtilisateur()->getAge();

            if (!isset($sommeByAge[$age])) {
                $sommeByAge[$age] = 0;
                $nbByAge[$age] = 0;
            }

            // Additionne le stress pour chaque âge
            $sommeByAge[$age] += $totalStress;
            // Compte le nombre de diagnostics par âge
            $nbByAge[$age]++;
        }

        // Calcule la moyenne par âge
        $moyenneByAge = [];
        foreach ($sommeByAge as $age => $somme) {
            $moyenneByAge[$age] = $somme / $nbByAge[$age];
        }

        // Trie par âge
        ksort($moyenneByAge);

        return [
            'age' => array_keys($moyenneByAge),
            'data' => array_values($moyenneByAge)
        ];
    }

    private function getChartEventsByCategorie(array $diagnostics): array
    {
        $categoryStats = [];

        foreach ($diagnostics as $diagnostic) {
            foreach ($diagnostic->getReponses() as $reponse) {
                foreach ($reponse->getEvents() as $event) {
                    $categoryName = $event->getCategorie()->getLibelle();
                    $categoryStats[$categoryName] = ($categoryStats[$categoryName] ?? 0) + 1;
                }
            }
        }

        // Trier par nom de catégorie
        ksort($categoryStats);

        return [
            'labels' => array_keys($categoryStats),
            'data' => array_values($categoryStats)
        ];
    }

    private function getChartEventsByCategorieAndAge(array $diagnostics): array
    {
        $eventsByAgeAndCategory = [];
        $categories = [];

        foreach ($diagnostics as $diagnostic) {
            $age = $diagnostic->getUtilisateur()->getAge();

            if (!isset($eventsByAgeAndCategory[$age])) {
                $eventsByAgeAndCategory[$age] = [];
            }

            foreach ($diagnostic->getReponses() as $reponse) {
                foreach ($reponse->getEvents() as $event) {
                    $categoryName = $event->getCategorie()->getLibelle();

                    // Ajouter la catégorie à notre liste de catégories uniques
                    if (!in_array($categoryName, $categories)) {
                        $categories[] = $categoryName;
                    }

                    // Incrémenter le compteur pour cette catégorie à cet âge
                    if (!isset($eventsByAgeAndCategory[$age][$categoryName])) {
                        $eventsByAgeAndCategory[$age][$categoryName] = 0;
                    }
                    $eventsByAgeAndCategory[$age][$categoryName]++;
                }
            }
        }

        ksort($eventsByAgeAndCategory);

        $ages = array_keys($eventsByAgeAndCategory);
        $datasets = [];

        $colors = [
            'rgba(255, 99, 132, 0.8)',   // Rouge
            'rgba(54, 162, 235, 0.8)',   // Bleu
            'rgba(255, 206, 86, 0.8)',   // Jaune
            'rgba(75, 192, 192, 0.8)',   // Vert clair
            'rgba(153, 102, 255, 0.8)',  // Violet
            'rgba(255, 159, 64, 0.8)',   // Orange
            'rgba(199, 199, 199, 0.8)',  // Gris
            'rgba(83, 102, 255, 0.8)',   // Bleu-violet
            'rgba(255, 99, 255, 0.8)',   // Rose
            'rgba(99, 255, 132, 0.8)',   // Vert
        ];

        // Préparer un dataset pour chaque catégorie
        foreach ($categories as $index => $category) {
            $categoryData = [];

            foreach ($ages as $age) {
                $categoryData[] = $eventsByAgeAndCategory[$age][$category] ?? 0;
            }

            $colorIndex = $index % count($colors);

            $datasets[] = [
                'label' => $category,
                'data' => $categoryData,
                'backgroundColor' => $colors[$colorIndex],
                'borderColor' => str_replace('0.8', '1', $colors[$colorIndex]),
                'borderWidth' => 1,
                'yAxisID' => 'y',
            ];
        }

        return [
            'ages' => $ages,
            'datasets' => $datasets,
            'categories' => $categories
        ];
    }

    #[Route('/new/event', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            $this->em->persist($event);
            $this->em->flush();
        }

        return $this->render('Admin/Modal/EventAdd.html.twig', [
            'event' => $event,
            'form' => $form,
            'action' => $this->generateUrl('app_event_new', ['id' => $event->getId()]),
        ]);
    }

    #[Route('/event/{id}/edit', name: 'app_edit_event', methods: ['GET', 'POST'])]
    public function edit(Event $event, Request $request): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            $this->em->persist($event);
            $this->em->flush();
        }

        return $this->render('Admin/Modal/EventEdit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'action' => $this->generateUrl('app_edit_event', ['id' => $event->getId()]),
        ]);
    }

    #[Route('/event/{id}/confirm-delete', name: 'app_confirm_delete_event')]
    public function confirmDeleteEvent(Event $event)
    {
        return $this->render('Admin/Modal/confirmDeleteEvent.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'app_delete_event', methods: ['GET', 'POST'])]
    public function deleteEvent(Event $event)
    {
        try {
            $this->em->remove($event);
            $this->em->flush();

            return $this->render('succesModal.html.twig', [
                'titre' => 'Suppression réussie',
                'btn' => 'Retour au tableau de bord',
                'action' => $this->generateUrl('app_admin_dashboard'),
            ]);
        } catch (\Exception $e) {
            return $this->render('errorModal.html.twig', [
                'titre' => 'Erreur lors de la suppression',
                'message' => 'Une erreur est survenue lors de la suppression.',
                'btn' => 'Retour au tableau de bord',
                'action' => $this->generateUrl('app_admin_dashboard'),
            ]);
        }
    }

    #[Route('/user/{id}/confirm-mail-renisialisation-Password', name: 'app_confirm_mail_renisialisation_password_admin')]
    public function confirmMailRenisialisationPassword(Utilisateur $utilisateur)
    {
        return $this->render('Admin/Modal/confirmationMailResetPassword.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/user/{id}/mail-renisialisation-Password', name: 'app_mail_renisialisation_password_admin')]
    public function renisialisationPassword
    (
        Utilisateur $utilisateur,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        ServiceMail $serviceMail
    ): Response {
            if ($utilisateur) {
                $token = $tokenGenerator->generateToken();
                $code = random_int(100000, 999999);
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($utilisateur);
                $resetPassword->setToken($token);
                $resetPassword->setCreatedAt(new \DateTimeImmutable());
                $resetPassword->setCode($code);

                $entityManager->persist($resetPassword);
                $entityManager->flush();

                $emails = $utilisateur->getEmail();

                $url = $this->generateUrl('app_verifi_code_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                try {
                    $serviceMail->send(
                        'noreply@cesizen.com',
                        'Réinitialisation de mot de passe',
                        (array)$emails,
                        [],
                        'Mail/resetPasswordAdmin.html.twig',
                        [],[
                            'url' => $url,
                            'token' => $token,
                            'code'  => $code,
                            'user'  => $utilisateur,
                        ]
                    );

                    $entityManager->persist($resetPassword);
                    $entityManager->flush();

                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé');

                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    $this->addFlash('error', sprintf('Erreur lors de l\'envoi de l\'email : %s', $e->getMessage()));
                    return $this->render('Mail/messageEnvoiEmail.html.twig', [
                        'titre' => 'Error lors de l\'envoi',
                        'text' => $e->getMessage(),
                        'btn' => 'Retour au tableau de bord',
                        'action' => $this->generateUrl('app_admin_dashboard'),
                    ]);
                }

            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email');
            }

        return $this->render('Mail/messageEnvoiEmail.html.twig', [
            'titre' => 'Réinitialisation de mot de passe',
            'text' => 'Un email de réinitialisation a été envoyé à l\'utilisateur.',
            'btn' => 'Retour au tableau de bord',
            'action' => $this->generateUrl('app_admin_dashboard'),
        ]);
    }

    #[Route('/test', name: 'app_api_test')]
    public function testApi(): Response
    {
        return $this->render('Api/testApi.html.twig');
    }
}