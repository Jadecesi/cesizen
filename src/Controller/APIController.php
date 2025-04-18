<?php

namespace App\Controller;

use App\Repository\ContenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class APIController extends AbstractController
{
    #[Route('/contenus', name: 'api_contenu', methods: ['GET'])]
    public function APIContenu(ContenuRepository $contenuRepository): JsonResponse {

        $contenus = $contenuRepository->findAll();

        return $this->json($contenus, 200, [], ['groups' => 'api_contenu']);
    }
}