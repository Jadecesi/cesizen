<?php

namespace App\Controller;

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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            dump($error);
            dump($lastUsername);
            $this->addFlash('error', 'Identifiants incorrects.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }


    #[Route(path: '/signup', name: 'app_signup')]
    public function signup(Request $request, EntityManagerInterface $entityManager, RoleRepository $roleRepository, UserAuthenticatorInterface $userAuthenticator, AuthentificationAuthenticator $authenticator, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(SignupType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if ($user['password'] !== $user['confirmPassword']) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');

                return $this->render('security/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $userExiste = $utilisateurRepository->findOneBy(['email' => $user['email']]);

            if (!empty($userExiste)) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà');

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
            $utilisateur->setPassword($userPasswordHasher->hashPassword($utilisateur, $user['password']));
            $utilisateur->setRole($role);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

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
