<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Utilisateur;
use App\Form\Security\LoginType;
use App\Form\Security\SignupType;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Security\AuthentificationAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

#[AllowDynamicProperties] class SecurityController extends AbstractController
{
    private RoleRepository $roleRepository;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct
    (
        RoleRepository $roleRepository,
        UtilisateurRepository $utilisateurRepository
    )
    {
        $this->roleRepository = $roleRepository;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/signup', name: 'app_signup')]
    public function signup(Request $request, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticatorInterface, AuthentificationAuthenticator $authenticator): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(SignupType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du mot de passe
            if ($utilisateur->getPassword() !== $form->get('confirmPassword')->getData()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');

                return $this->render('security/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Vérification de l'email unique
            $userExiste = $this->utilisateurRepository->findOneBy(['email' => $utilisateur->getEmail()]);
            if ($userExiste !== null) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà');

                return $this->render('security/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Attribution du rôle
            $role = $this->roleRepository->findOneBy(['nom' => 'Utilisateur']);
            $utilisateur->setRole($role);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');

            return $userAuthenticatorInterface->authenticateUser(
                $utilisateur,
                $authenticator,
                $request
            );
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
