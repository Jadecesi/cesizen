<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\Security\LoginType;
use App\Form\Security\SignupType;
use App\Repository\RoleRepository;
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

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
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
    public function signup(Request $request, EntityManagerInterface $entityManager, RoleRepository $roleRepository, UserAuthenticatorInterface $userAuthenticator, AuthentificationAuthenticator $authenticator): Response
    {
        dump($request->request->all());
        $form = $this->createForm(SignupType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump('Form submitted and valid');
            $user = $form->getData();

            if ($user['password'] !== $user['confirmPassword']) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');

                return $this->render('security/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $role = $roleRepository->findOneBy(['nom' => 'Utilisateur']);

            $utilisateur = new Utilisateur();
            $utilisateur->setPrenom($user['prenom']);
            $utilisateur->setNom($user['nom']);
            $utilisateur->setUsername($user['username']);
            $utilisateur->setEmail($user['email']);
            $utilisateur->setDateNaissance($user['dateNaissance']);
            $utilisateur->setPassword($user['confirmPassword']);
            $utilisateur->setRole($role);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');

            return $userAuthenticator->authenticateUser(
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
