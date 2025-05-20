<?php

namespace App\Controller;

use App\Repository\ContenuRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api')]
class APIController extends AbstractController
{
    #[Route('/contenus', name: 'api_contenu', methods: ['GET'])]
    public function APIContenu(ContenuRepository $contenuRepository): JsonResponse {

        $contenus = $contenuRepository->findAll();

        return $this->json($contenus, 200, [], ['groups' => 'api_contenu']);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UtilisateurRepository $users, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Body JSON non conform'], 400);
        }

        $user = $users->findOneBy(['email' => $data['email']]);

        if (!$user) {
            $user = $users->findOneBy(['username' => $data['email']]);
        }

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identified ou mot de passe incorrect'], 401);
        }

        $token = Uuid::v4()->toRfc4122() . bin2hex(random_bytes(32));
        dump($token);
        $user->setApiToken($token)
            ->setTokenExpiresAt(new \DateTimeImmutable('+7 days'));
        $em->flush();

        return $this->json($user, 200, [], ['groups' => 'api_user']);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if ($user) {
            $user->setApiToken(null)->setTokenExpiresAt(null);
            $em->flush();
        }
        return $this->json(['message' => 'Logged out']);
    }
}