<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileType;
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

            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setDateNaissance($dateNaissance);
            $user->setUsername($username);

            dump($photoProfile);

            $existingPhotoProfile = $user->getPhotoProfile();
            $uploadDir = $this->getParameter('profile_pictures_directory');

            if ($photoProfile instanceof UploadedFile) {
                $newFilename = uniqid() . '.' . $photoProfile->getClientOriginalExtension();

                try {
                    // Déplacement du fichier d'abord
                    $photoProfile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );

                    // Si le déplacement réussit, on supprime l'ancienne photo
                    if (!empty($existingPhotoProfile)) {
                        $oldPath = $uploadDir . '/' . $existingPhotoProfile;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $user->setPhotoProfile($newFilename);
                    $entityManager->flush();
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
