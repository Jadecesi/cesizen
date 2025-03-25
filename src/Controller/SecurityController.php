<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\Security\SignupType;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Security\AuthentificationAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->addFlash('error', 'Identifiants incorrects.');
        }

        return $this->render('Security/login.html.twig', [
            'last_username' => $lastUsername
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/signup', name: 'app_signup')]
    public function signup(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UtilisateurRepository $utilisateurRepository, RoleRepository $roleRepository, UserPasswordHasherInterface $userPasswordHasher, AuthentificationAuthenticator $authenticator, UserAuthenticatorInterface $userAuthenticator): Response {

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

            // Gestion de l'image de profil
            /** @var UploadedFile $profilePicture */
            $profilePicture = $form->get('profilePicture')->getData();
            $defaultPicture = $form->get('defaultProfilePicture')->getData();

            if ($profilePicture) {
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();

                try {
                    $profilePicture->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    $utilisateur->setPhotoProfile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            } elseif ($defaultPicture) {
                // Si l'utilisateur a sélectionné une image par défaut
                $utilisateur->setPhotoProfile($defaultPicture);
            } else {
                // Si aucune image n'est sélectionnée, mettre une image par défaut
                $utilisateur->setPhotoProfile('default.png');
            }

            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $form->get('password')->getData());
            $utilisateur->setPassword($hashedPassword);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Compte créé avec succès !');

            return $userAuthenticator->authenticateUser(
                $utilisateur,
                $authenticator,
                $request
            );
        }

        return $this->render('Security/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
