<?php

namespace App\Controller;

use App\Entity\Diagnostic;
use App\Entity\Reponse;
use App\Form\QuestionnaireType;
use App\Repository\DiagnosticRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/diagnostic')]
class DiagnosticController extends AbstractController
{
    #[Route('/', name: 'app_diagnostic_index', methods: ['GET', 'POST'])]
    public function index(DiagnosticRepository $diagnosticRepository): Response
    {
        $user = $this->getUser();
        dump($user);

        if (!empty($user)) {
            return $this->redirectToRoute('app_diagnostic_user_index');
        }

        return $this->render('Diagnostic/diagnostic.html.twig', [
            'diagnostics' => null,
        ]);
    }

    #[Route('/user', name: 'app_diagnostic_user_index', methods: ['GET', 'POST'])]
    public function indexUser(DiagnosticRepository $diagnosticRepository): Response
    {
        $user = $this->getUser();
        $diagnostics = $diagnosticRepository->getAllDiagnosticsByUser($user);

        return $this->render('Diagnostic/diagnostic.html.twig', [
            'diagnostics' => $diagnostics,
        ]);
    }



    #[Route('/new', name: 'app_diagnostic_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stress = [];
        $form = $this->createForm(QuestionnaireType::class);
        $form->handleRequest($request);

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

                return $this->redirectToRoute('app_diagnostic_index');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du diagnostic.');
            return $this->redirectToRoute('app_diagnostic_index');
        }

        return $this->render('Diagnostic/new.html.twig', [
            'form' => $form->createView(),
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
}