<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\Utilisateur;
use App\Form\Security\ResetPasswordType;
use App\Form\Security\RequestForgotPasswordType;
use App\Form\Security\SignupType;
use App\Form\Security\VerificationTokenPasswordType;
use App\Repository\ResetPasswordRepository;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Security\AuthentificationAuthenticator;
use App\Service\ServiceMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    private ResetPasswordRepository $resetPasswordRepository;

    public function __construct
    (
        ResetPasswordRepository $resetPasswordRepository
    )
    {
        $this->resetPasswordRepository = $resetPasswordRepository;
    }

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

            $role = $roleRepository->findOneBy(['nom' => 'ROLE_USER']);

            $utilisateur = new Utilisateur();
            $utilisateur->setPrenom($user['prenom']);
            $utilisateur->setNom($user['nom']);
            $utilisateur->setUsername($user['username']);
            $utilisateur->setEmail($user['email']);
            $utilisateur->setDateNaissance($user['dateNaissance']);
            $utilisateur->setPassword($userPasswordHasher->hashPassword($utilisateur, $user['password']));
            $utilisateur->setRole($role);
            $utilisateur->setIsActif(true);

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

            dump($utilisateur);
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

    #[Route('/access-denied-public', name: 'app_access_denied_public')]
    public function accessDeniedPublic(): Response
    {
        return $this->render('Security/access_denied.html.twig');
    }

    #[Route('/request-reset-password', name: 'app_forgot_password_request')]
    public function request
    (
        Request $request,
        TokenGeneratorInterface $tokenGenerator,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager,
        ServiceMail $serviceMail
    ): Response {
        $user = $this->getUser();
        if ($user) {
           $email = $user->getEmail();
        } else {
            $email = null;
        }

        $form = $this->createForm(RequestForgotPasswordType::class, null, [
            'email' => $email
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mail = $form->get('mail')->getData();
            $utilisateur = $utilisateurRepository->findOneBy(['email' => $mail]);

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

                $emails = $utilisateur->getEMail();

                try {
                    $serviceMail->send(
                        'noreply@cesizen.com',
                        'Réinitialisation de mot de passe',
                        (array)$emails,
                        [],
                        'mail/resetPasswordUser.html.twig',
                        [],[
                            'token' => $token,
                            'code'  => $code,
                            'user'  => $utilisateur,
                        ]
                    );

                    $entityManager->persist($resetPassword);
                    $entityManager->flush();

                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé');
                    return $this->redirectToRoute('app_verifi_code_password', [
                        'token' => $token
                    ]);

                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    $this->addFlash('error', sprintf('Erreur lors de l\'envoi de l\'email : %s', $e->getMessage()));
                    return $this->redirectToRoute('app_forgot_password_request');
                }

            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email');
            }
        }

        return $this->render('Security/resetPasswordRequest.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{token}/token/verification', name: 'app_verifi_code_password')]
    public function verificationCodeForgotPassword (
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        // Recherche du token dans la base de données
        $resetPassword = $this->resetPasswordRepository->findOneBy(['token' => $token]);

        // Vérifie si le token existe et n'a pas expiré (1 heure)
        if (!$resetPassword ||
            $resetPassword->getCreatedAt()->modify('+1 hour') < new \DateTimeImmutable()) {
            if ($resetPassword) {
                $entityManager->remove($resetPassword);
                $entityManager->flush();
            }
            $this->addFlash('error', 'Ce lien de réinitialisation n\'est plus valide');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(VerificationTokenPasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();

            if ($code) {

                if ($code === (string)$resetPassword->getCode()) {
                    $this->addFlash('success', 'Code de vérification valide');

                    return $this->redirectToRoute('app_reset_password', [
                        'token' => $token
                    ]);
                } else {
                    $this->addFlash('error', 'Code de vérification invalide');
                }

            } else {
                $this->addFlash('error', 'Veuillez entrer le code de vérification');
            }
        }

        return $this->render('Security/verificationCodePassword.html.twig', [
            'form' => $form->createView(),
            'token' => $token
        ]);
    }

    #[Route('/{token}/token/new-password', name: 'app_reset_password')]
    public function resetPassword
    (
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuthentificationAuthenticator $authenticator,
        UserAuthenticatorInterface $userAuthenticator,
    ) : Response {
        $form = $this->createForm(ResetPasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $password = $form->get('password')->getData();
            $resetPassword = $this->resetPasswordRepository->findOneBy(['token' => $token]);

            if ($data['password'] !== $data['confirmPassword']) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');

                return $this->redirectToRoute('app_reset_password', [
                    'token' => $token
                ]);
            }

            if ($password) {
                $user = $resetPassword->getUser();

                // Hash du nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Suppression du token
                $entityManager->remove($resetPassword);
                $entityManager->flush();

                // Connexion de l'utilisateur
                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès');

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );

            } else {
                $this->addFlash('error', 'Veuillez entrer un mot de passe valide');
            }
        }

        return $this->render('Security/resetPassword.html.twig', [
            'token' => $token,
            'form' => $form->createView()
        ]);
    }
}
