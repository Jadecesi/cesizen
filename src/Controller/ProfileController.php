<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
class ProfileController extends AbstractController
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct
    (
        UtilisateurRepository $utilisateurRepository
    )
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route('/', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var UploadedFile|null $photoProfile */
            $photoProfile = $form->get('photoProfile')->getData();
            $nom = $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();
            $email = $form->get('email')->getData();
            $dateNaissance = $form->get('dateNaissance')->getData();
            $username = $form->get('username')->getData();

            if ($email !== $user->getEmail()) {
                dump('email différent de selui de l\'utilisateur');
                $emailExiste = $this->utilisateurRepository->findOneBy(['email' => $email]);

                if ($emailExiste) {
                    $this->addFlash('error', 'Cet email est déjà utilisé.');

                    return $this->render('User/profile.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setDateNaissance($dateNaissance);
            $user->setUsername($username);



            $existingPhotoProfile = $user->getPhotoProfile();
            $uploadDir = $this->getParameter('profile_pictures_directory');

            if ($photoProfile === null) {
                $this->addFlash('success', "Utilisateur à était modifiés avec succès.");
                $entityManager->flush();

                return $this->render('User/profile.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            if ($photoProfile instanceof UploadedFile) {
                $newFilename = uniqid() . '.' . $photoProfile->getClientOriginalExtension();

                try {
                    // Déplacement du fichier d'abord
                    $photoProfile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );

                    $isExistePhotoDefault = str_contains($existingPhotoProfile, 'profilePicture');

                    // Si le déplacement réussit, on supprime l'ancienne photo
                    if (!empty($existingPhotoProfile) && $isExistePhotoDefault === false) {
                        $oldPath = $uploadDir . '/' . $existingPhotoProfile;
                        if (file_exists($oldPath)) {
                            dump('supprimer la phtoto');
                            unlink($oldPath);
                        }
                    }

                    $user->setPhotoProfile($newFilename);
                    $entityManager->flush();
                    $this->addFlash('success', "Utilisateur et la phtoto de profil modifiés avec succès.");
                } catch (FileException $e) {
                    $logger->error('Erreur upload : ' . $e->getMessage());
                    foreach ($form->getErrors(true) as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                    return $this->redirectToRoute('app_profile');
                }
            }
        }

        return $this->render('User/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
