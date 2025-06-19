<?php

namespace App\Controller;

use App\Entity\Contenu;
use App\Entity\Diagnostic;
use App\Entity\Utilisateur;
use App\Repository\ContenuRepository;
use App\Repository\DiagnosticRepository;
use App\Repository\EventRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api')]
class APIController extends AbstractController
{
    private EntityManagerInterface $em;
    private UtilisateurRepository $utilisateurRepository;
    private EventRepository $eventRepository;

    public function __construct
    (
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        EventRepository $eventRepository
    )
    {
        $this->em = $em;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/contenus', name: 'api_contenu', methods: ['GET'])]
    public function APIContenu(ContenuRepository $contenuRepository): JsonResponse {

        $contenus = $contenuRepository->findAll();

        return $this->json($contenus, 200, [], ['groups' => 'api_contenu']);
    }

    #[Route('/contenu/{id}', name: 'api_contenu_id', methods: ['GET'])]
    public function APIContenuId(int $id, ContenuRepository $contenuRepository): JsonResponse {

        $contenu = $contenuRepository->find($id);

        if (!$contenu) {
            return $this->json(['error' => 'Contenu not found'], 404);
        }

        return $this->json($contenu, 200, [], ['groups' => 'api_contenu']);
    }

    #[Route('/contenus/new', name: 'api_contenu_new', methods: ['POST'])]
    public function APIContenuNew(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        // Récupération du token depuis le header
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return $this->json(['error' => 'Token manquant'], 401);
        }

        // Extraction du token (format "Bearer <token>")
        $token = str_replace('Bearer ', '', $authHeader);

        error_log("Token reçu: " . $token);

        // Récupération de l'utilisateur à partir du token
        $utilisateur = $utilisateurRepository->findOneBy(['apiToken' => $token]);
        error_log("Utilisateur trouvé: ID=" . ($utilisateur ? $utilisateur->getId() : 'null'));

        if (!$utilisateur) {
            return $this->json(['error' => 'Token invalide'], 401);
        }

        // Vérification du rôle admin
        if (!in_array('ROLE_ADMIN', $utilisateur->getRoles())) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        error_log("Données reçues: " . json_encode($data));

        // Validation des champs requis
        if (!$data || !isset($data['titre'], $data['description'], $data['image'])) {
            return $this->json(['error' => 'Les champs titre, description et image sont requis'], 400);
        }

        try {
            $contenu = new Contenu();
            $contenu->setTitre($data['titre']);
            $contenu->setDescription($data['description']);
            $contenu->setDateCreation(new \DateTimeImmutable());
            $contenu->setUtilisateur($utilisateur);

            if (str_starts_with($data['image'], 'http')) {
                $contenu->setImage($data['image']);
            } else {
                $imageName = basename($data['image']);
                $contenu->setImage($imageName);
            }

            if (!empty($data['url'])) {
                $contenu->setUrl($data['url']);
            }

            error_log("Before persist - Contenu: " . json_encode([
                    'titre' => $contenu->getTitre(),
                    'description' => $contenu->getDescription(),
                    'image' => $contenu->getImage(),
                    'url' => $contenu->getUrl()
                ]));

            $this->em->persist($contenu);
            $this->em->flush();

            error_log("After flush - Contenu ID: " . $contenu->getId());

            return $this->json($contenu, 201, [], [
                'groups' => 'api_contenu',
                'json_encode_options' => JSON_UNESCAPED_SLASHES
            ]);

        } catch (\Exception $e) {
            error_log("Error creating content: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " on line " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->json([
                'error' => 'Erreur lors de la création du contenu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UtilisateurRepository $users, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Body JSON non conforme'], 400);
        }

        $user = $users->findOneBy(['email' => $data['email']]);

        if (!$user) {
            $user = $users->findOneBy(['username' => $data['email']]);
        }

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identifiants ou mot de passe incorrect'], 401);
        }

        // Vérification si le compte est actif
        if (!$user->isActif()) {
            return $this->json(['error' => 'Votre compte a été désactivé'], 403);
        }

        $token = Uuid::v4()->toRfc4122() . bin2hex(random_bytes(32));
        $user->setApiToken($token)
            ->setTokenExpiresAt(new \DateTimeImmutable('+7 days'));

        try {
            $em->flush();
            return $this->json($user, 200, [], ['groups' => 'api_user']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la connexion'], 500);
        }
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

    #[Route('/user/{id}', name: 'api_login', methods: ['GET'])]
    public function APIUserId(int $id, UtilisateurRepository $users): JsonResponse
    {
        $user = $users->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        return $this->json($user, 200, [], ['groups' => 'api_user']);
    }

    #[Route('/diagnostic', name: 'api_diagnostic', methods: ['GET'])]
    public function APIDiagnostic(DiagnosticRepository $diagnosticRepository): JsonResponse
    {
        $diagnostics = $diagnosticRepository->findBy([], ['dateCreation' => 'DESC']);

        return $this->json($diagnostics, 200, [], ['groups' => 'api_diagnostic']);
    }

    #[Route('/diagnostic/{id}', name: 'api_diagnostic_id', methods: ['GET'])]
    public function APIDiagnosticId(int $id, DiagnosticRepository $diagnosticRepository): JsonResponse
    {
        $diagnostic = $diagnosticRepository->find($id);

        if (!$diagnostic) {
            return $this->json(['error' => 'Diagnostic not found'], 404);
        }

        return $this->json($diagnostic, 200, [], ['groups' => 'api_diagnostic']);
    }

    #[Route('/events', name: 'api_events', methods: ['GET'])]
    public function APIEvents(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        return $this->json($events, 200, [], ['groups' => 'api_event']);
    }

    #[Route('/diagnostic/new-user', name: 'api_diagnostic_user_new', methods: ['POST'])]
    public function APIDiagnosticNewUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            // Validation des données
            if (!isset($data['utilisateur_id']) || !isset($data['selected_events'])) {
                return $this->json(['error' => 'Données manquantes'], 400);
            }

            // Récupérer l'utilisateur
            $utilisateur = $this->utilisateurRepository->find($data['utilisateur_id']);
            if (!$utilisateur) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            // Créer le diagnostic
            $diagnostic = new Diagnostic();
            $diagnostic->setUtilisateur($utilisateur);
            $diagnostic->setDateCreation(new \DateTimeImmutable());

            // Ajouter les événements
            $stress = 0;
            foreach ($data['selected_events'] as $eventId) {
                $event = $this->eventRepository->find($eventId);
                if (!$event) {
                    return $this->json(['error' => 'Événement non trouvé: ' . $eventId], 404);
                }
                $diagnostic->addEvent($event);
                $stress += $event->getStress();
            }

            $diagnostic->setTotalStress($stress);

            // Persister les entités
            $this->em->persist($diagnostic);
            $this->em->flush();

            return $this->json($diagnostic, 201, [], ['groups' => 'api_diagnostic']);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création du diagnostic',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/diagnostic/user/{id}', name: 'api_diagnostic_user', methods: ['GET'])]
    public function APIDiagnosticByUser(Utilisateur $userId, DiagnosticRepository $diagnosticRepository): JsonResponse
    {
        $diagnostics = $diagnosticRepository->findBy(['utilisateur' => $userId], ['dateCreation' => 'DESC']);

        if (!$diagnostics) {
            return $this->json(['error' => 'No diagnostics found for this user'], 404);
        }

        return $this->json($diagnostics,200, [], ['groups' => 'api_diagnostic']);
    }

    #[Route('/users', name: 'api_user', methods: ['GET'])]
    public function APIUser(UtilisateurRepository $utilisateurRepository, Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return $this->json(['error' => 'Token manquant'], 401);
        }

        // Extraction du token (format "Bearer <token>")
        $token = str_replace('Bearer ', '', $authHeader);

        error_log("Token reçu: " . $token);

        // Récupération de l'utilisateur à partir du token
        $utilisateur = $utilisateurRepository->findOneBy(['apiToken' => $token]);
        error_log("Utilisateur trouvé: ID=" . ($utilisateur ? $utilisateur->getId() : 'null'));

        if (!$utilisateur) {
            return $this->json(['error' => 'Token invalide'], 401);
        }

        // Vérification du rôle admin
        if (!in_array('ROLE_ADMIN', $utilisateur->getRoles())) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }

        $users = $utilisateurRepository->findAll();

        return $this->json($users, 200, [], ['groups' => 'api_user']);
    }
    #[Route('/user/{id}', name: 'api_user_id', methods: ['GET'])]
    public function APIUserById(int $id, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $user = $utilisateurRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouver'], 404);
        }
        return $this->json($user, 200, [], ['groups' => 'api_user']);
    }

    #[Route('/user/{id}/statut', name: 'api_user_statut', methods: ['PATCH'])]
    public function APIToggleUserStatus(Utilisateur $utilisateur): JsonResponse {
        try {
            $user = $this->utilisateurRepository->find($utilisateur);

            if (!$user) {
                return $this->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Inverse l'état actif de l'utilisateur
            $user->setIsActif(!$user->isActif());

            $this->em->persist($user);
            $this->em->flush();

            return $this->json([
                'message' => 'Statut de l\'utilisateur mis à jour avec succès',
                'isActif' => $user->isActif()
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/user/{id}/delete', name: 'api_user_delete', methods: ['DELETE'])]
    public function APIDeleteUser(Utilisateur $utilisateur, Request $request): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $token = str_replace('Bearer ', '', $authHeader);

            $userToken = $this->utilisateurRepository->findOneBy(['apiToken' => $token]);

            if ($userToken->getid() !== $utilisateur->getId()) {
                return $this->json(['error' => 'Vous ne pouvez pas supprimer un utilisateur qui n\'est pas le vôtre'], 403);
            }

            return $this->json(['error' => 'Token manquant'], 401);
        }

        try {
            $this->em->remove($utilisateur);
            $this->em->flush();

            return $this->json(['success' => 'Compte utilisateur supprimer avec success'], 200);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/diagnostic/{id}/delete', name: 'api_diagnostic_delete', methods: ['DELETE'])]
    public function APIDeleteDiagnostic(Diagnostic $diagnostic, Request $request): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $token = str_replace('Bearer ', '', $authHeader);

            $userToken = $this->utilisateurRepository->findOneBy(['apiToken' => $token]);

            $idUtilisateur = $diagnostic->getUtilisateur()->getId();

            if ($userToken->getid() !== $idUtilisateur) {
                return $this->json(['error' => 'Vous ne pouvez pas supprimer un diagnostic qui n\'est pas le vôtre'], 403);
            }

            return $this->json(['error' => 'Token manquant'], 401);
        }

        try {
            $this->em->remove($diagnostic);
            $this->em->flush();

            return $this->json(['success' => 'Le diagnostic a était supprimer avec success'], 200);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la suppression du diagnostic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/user/{id}/edit', name: 'api_user_edit', methods: ['PATCH'])]
    public function APIEditProfil(Utilisateur $utilisateur, Request $request): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $token = str_replace('Bearer ', '', $authHeader);

            $userToken = $this->utilisateurRepository->findOneBy(['apiToken' => $token]);

            if ($userToken->getid() !== $utilisateur->getId()) {
                return $this->json(['error' => 'Vous ne pouvez pas supprimer un diagnostic qui n\'est pas le vôtre'], 403);
            }

            return $this->json(['error' => 'Token manquant'], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $nom = $data['nom'];
            $prenom = $data['prenom'];
            $email = $data['email'];
            $username = $data['username'];

            $utilisateur->setNom($nom)
                ->setPrenom($prenom)
                ->setEmail($email)
                ->setUsername($username);

            $this->em->persist($utilisateur);
            $this->em->flush();

            return $this->json($utilisateur, 200, [], ['groups' => 'api_user']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/contenu/{id}/edit', name: 'api_contenu_edit', methods: ['PATCH'])]
    public function APIEditContenul(Contenu $contenu, Request $request): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        $token = str_replace('Bearer ', '', $authHeader);

        $userToken = $this->utilisateurRepository->findOneBy(['apiToken' => $token]);

        if (!$authHeader) {
            if ($userToken->getRole() !== 'ROLE_ADMIN') {
                return $this->json(['error' => 'Vous n\'avez pas les droits pour modifier un contenu '], 403);
            }

            return $this->json(['error' => 'Token manquant'], 401);
        }

        error_log("Token reçu: " . $authHeader);
        error_log("Utilisateur trouvé: ID=", $userToken->getId());

        try {
            $data = json_decode($request->getContent(), true);

            $titre = $data['titre'];
            $utilisateur = $userToken;
            $description = $data['description'];
            $url = $data['url'] ?? null;

            $contenu->setTitre($titre)
                ->setDescription($description)
                ->setDateModification()
                ->setUtilisateur($utilisateur);

            if ($url !== null) {
                $contenu->setUrl($url);
            }

            $this->em->persist($contenu);
            $this->em->flush();

            return $this->json($contenu, 200, [], ['groups' => 'api_contenu']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du contenu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/contenu/{id}/delete', name: 'api_contenu_delete', methods: ['DELETE'])]
    public function APIDeleteContenu(Contenu $contenu, Request $request): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return $this->json(['error' => 'Token manquant'], 401);
        }

        try {
            $this->em->remove($contenu);
            $this->em->flush();

            return $this->json(['success' => 'Le diagnostic a était supprimer avec success'], 200);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la suppression du diagnostic',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}