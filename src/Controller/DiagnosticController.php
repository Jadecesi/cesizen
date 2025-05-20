<?php

namespace App\Controller;

use App\Entity\Diagnostic;
use App\Entity\Reponse;
use App\Form\QuestionnaireType;
use App\Repository\CategorieEventRepository;
use App\Repository\DiagnosticRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/diagnostic')]
class DiagnosticController extends AbstractController
{
    private DiagnosticRepository $diagnosticRepository;

    public function __construct
    (
        DiagnosticRepository $diagnosticRepository
    )
    {
        $this->diagnosticRepository = $diagnosticRepository;
    }

    #[Route('/', name: 'app_diagnostic_index', methods: ['GET', 'POST'])]
    public function index(CategorieEventRepository $categorieEventRepository, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $categories = $categorieEventRepository->findAll();

        $eventsByCategory = [];
        foreach ($categories as $category) {
            $eventsByCategory[$category->getId()] = $eventRepository->findByCategory($category->getId());
        }

        if (!empty($user)) {
            return $this->redirectToRoute('app_diagnostic_user_index');
        }

        return $this->render('Diagnostic/diagnostic.html.twig', [
            'diagnostics' => null,
            'categories' => $categories,
            'eventsByCategory' => $eventsByCategory
        ]);
    }

    #[Route('/user', name: 'app_diagnostic_user_index', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function indexUser(CategorieEventRepository $categorieEventRepository, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $diagnostics = $this->diagnosticRepository->getAllDiagnosticsByUser($user);

        $categories = $categorieEventRepository->findAll();

        $eventsByCategory = [];
        foreach ($categories as $category) {
            $eventsByCategory[$category->getId()] = $eventRepository->findByCategory($category->getId());
        }

        return $this->render('Diagnostic/diagnostic.html.twig', [
            'diagnostics' => $diagnostics,
            'categories' => $categories,
            'eventsByCategory' => $eventsByCategory,
        ]);
    }

    #[Route('/new', name: 'app_diagnostic_new', methods: ['GET', 'POST'])]
    public function newAnonymous(Request $request): Response
    {
        $stress = [];
        $form = $this->createForm(QuestionnaireType::class, [], ['user' => false]);
        $form->handleRequest($request);

        $action = $this->generateUrl('app_diagnostic_new');

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedEvents = $form->get('events')->getData();
            foreach ($selectedEvents as $event) {
                $stress[] = $event->getStress();
            }
            $totalStress = array_sum($stress);

            $diagnostic = new Diagnostic();
            $diagnostic->setTotalStress($totalStress);

            // Rediriger vers la page de résultats avec le score
            return $this->render('Diagnostic/result.html.twig', [
                'totalStress' => $totalStress,
                'events' => $selectedEvents,
                'diagnostic' => $diagnostic,
            ]);
        }

        return $this->render('Diagnostic/new.html.twig', [
            'form' => $form->createView(),
            'action' => $action
        ]);
    }

    #[Route('/new/user', name: 'app_diagnostic_new_user', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stress = [];
        $form = $this->createForm(QuestionnaireType::class, [], ['user' => true]);
        $form->handleRequest($request);

        $action = $this->generateUrl('app_diagnostic_new_user');

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                // Créer le diagnostic
                $diagnostic = new Diagnostic();
                $diagnostic->setUtilisateur($this->getUser());
                $entityManager->persist($diagnostic);

                // Créer la réponse et lier le diagnostic
                $reponse = new Reponse();
                $reponse->setDiagnostics($diagnostic);

                // Ajouter chaque événement sélectionné
                $selectedEvents = $form->get('events')->getData();
                foreach ($selectedEvents as $event) {
                    $reponse->addEvent($event);
                    $stress[] = $event->getStress();
                }

                // Calculer le total du stress
                $totalStress = array_sum($stress);
                $diagnostic->setTotalStress($totalStress);

                $entityManager->persist($reponse);
                $entityManager->flush();

                $this->addFlash('sucess', 'Diagnostic créé avec succès.');

                return $this->redirectToRoute('app_diagnostic_dashboard');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du diagnostic.');
            return $this->redirectToRoute('app_diagnostic_index');
        }

        return $this->render('Diagnostic/new.html.twig', [
            'form' => $form->createView(),
            'action' => $action
        ]);
    }

    #[Route('/user/dashboard', name: 'app_diagnostic_dashboard')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        $diagnostics = $this->diagnosticRepository->getAllDiagnosticsByUser($user);
        $facteurStress = $this->getFacteurStress($diagnostics);
        $evolutionStress = $this->getEvolutionStressByDate($diagnostics);

        $chartDataFacteurStress = [
            'labels' => $facteurStress['labels'],
            'data' => $facteurStress['data']
        ];

        $chartDataEvolutionStress = [
            'labels' => $evolutionStress['labels'],
            'data' => $evolutionStress['data'],
            'moyenneGenerale' => $evolutionStress['moyenneGenerale']
        ];

        if (empty($diagnostics)) {
            return $this->redirectToRoute('app_diagnostic_index');
        }

        return $this->render('Diagnostic/dashboard.html.twig', [
            'diagnostics' => $diagnostics,
            'chartDataFacteurStress' => $chartDataFacteurStress,
            'chartDataEvolutionStress' => $chartDataEvolutionStress,
        ]);
    }

    #[Route('/{id}', name: 'app_diagnostic_show', methods: ['GET'])]
    public function show(Diagnostic $diagnostic): Response
    {
        return $this->render('Diagnostic/show.html.twig', [
            'diagnostic' => $diagnostic,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_diagnostic_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Diagnostic $diagnostic, EntityManagerInterface $entityManager): Response
    {
        // Logique pour modifier un diagnostic

        return $this->render('Diagnostic/edit.html.twig', [
            'diagnostic' => $diagnostic,
        ]);
    }

    #[Route('/{id}', name: 'app_diagnostic_delete', methods: ['POST'])]
    public function delete(Request $request, Diagnostic $diagnostic, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$diagnostic->getId(), $request->request->get('_token'))) {
            $entityManager->remove($diagnostic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_diagnostic_index');
    }

    public function getFacteurStress(array $diagnostics)
    {
        $stats = [];
        foreach ($diagnostics as $diagnostic) {
            foreach ($diagnostic->getReponses() as $reponse) {
                foreach ($reponse->getEvents() as $event) {
                    $label = $event->getNom();
                    $stats[$label] = ($stats[$label] ?? 0) + 1;
                }
            }
        }
        arsort($stats);

        $stats = array_slice($stats, 0, 5, true);

        return [
            'labels' => array_keys($stats),
            'data' => array_values($stats)
        ];
    }

    public function getEvolutionStressByDate($diagnostics)
    {
        $stats = [];
        $moyenneGenerale = [];
        $timestamps = [];

        foreach ($diagnostics as $diagnostic) {
            $date = $diagnostic->getDateCreation();
            $dateFormatted = $date->format('d/m/Y');
            $timestamp = $date->getTimestamp();

            if (!isset($stats[$dateFormatted])) {
                $stats[$dateFormatted] = [
                    'somme' => 0,
                    'nombre' => 0,
                    'timestamp' => $timestamp
                ];
            }
            $stats[$dateFormatted]['somme'] += $diagnostic->getTotalStress();
            $stats[$dateFormatted]['nombre']++;
            $timestamps[$dateFormatted] = $timestamp;
        }

        $moyennes = [];
        foreach ($stats as $date => $data) {
            $moyennes[$date] = $data['somme'] / $data['nombre'];
            $moyenneGenerale[] = $data['somme'];
        }

        $moyenneGeneraleGlobale = count($moyenneGenerale) > 0 ? array_sum($moyenneGenerale) / count($stats) : 0;

        array_multisort($timestamps, SORT_ASC, $moyennes);

        return [
            'labels' => array_keys($moyennes),
            'data' => array_values($moyennes),
            'moyenneGenerale' => $moyenneGeneraleGlobale
        ];
    }
}