<?php

namespace App\Controller;


use App\Entity\Contenu;
use App\Form\ContenuType;
use App\Repository\ContenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contenu')]
class ContenuController extends AbstractController
{
    private ContenuRepository $contenuRepository;
    private EntityManagerInterface $em;

    public function __construct
    (
        ContenuRepository $contenuRepository,
        EntityManagerInterface $em
    )
    {
        $this->contenuRepository = $contenuRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'app_contenu_index', methods: ['GET'])]
    public function index()
    {
        $contenus = $this->contenuRepository->findAll();

        return $this->render('Contenu/index.html.twig', [
            'contenus' => $contenus
        ]);
    }

    #[Route('/admin/new', name: 'app_contenu_new', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        $form = $this->createForm(ContenuType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $contenu = $form->getData();

                /** @var UploadedFile $image */
                $image = $form->get('image')->getData();

                if ($image instanceof UploadedFile) {
                    try {
                        $newFilename = uniqid() . '.' . $image->getClientOriginalExtension();
                        $image->move(
                            $this->getParameter('contenu_pictures_directory'),
                            $newFilename
                        );

                        $user = $this->getUser();
                        $contenu->setUtilisateur($user);
                        $contenu->setImage($newFilename);

                        $this->em->persist($contenu);
                        $this->em->flush();

                        $this->addFlash('success', 'Le contenu a bien été ajouté.');
                        return $this->redirectToRoute('app_contenu_index');

                    } catch (FileException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            } else {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('Contenu/new.html.twig', [
            'form' => $form->createView(),
            'action' => $this->generateUrl('app_contenu_new'),
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_contenu_edit')]
    public function edit(Request $request, Contenu $contenu)
    {
        $form = $this->createForm(ContenuType::class, $contenu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateModification = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $contenu->setDateModification($dateModification);

            /** @var UploadedFile|null $image */
            $image = $form->get('image')->getData();

            if ($image instanceof UploadedFile) {
                try {
                    $newFilename = uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(
                        $this->getParameter('contenu_pictures_directory'),
                        $newFilename
                    );

                    // Supprimer l'ancienne image si elle existe
                    if ($contenu->getImage()) {
                        $oldFile = $this->getParameter('contenu_pictures_directory').'/'.$contenu->getImage();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $contenu->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }

            $this->em->persist($contenu);
            $this->em->flush();
            $this->addFlash('success', 'Le contenu a été modifié.');
            return $this->redirectToRoute('app_contenu_index');
        }

        return $this->render('Contenu/edit.html.twig', [
            'form' => $form->createView(),
            'contenu' => $contenu,
            'action' => $this->generateUrl('app_contenu_edit', ['id' => $contenu->getId()]),
        ]);
    }

    #[Route('/admin/{id}/delete', name: 'app_contenu_delete', methods: ['GET', 'POST'])]
    public function delete(Contenu $contenu): Response
    {
        try {
            // Supprimer l'image si elle existe
            if ($contenu->getImage()) {
                $imagePath = $this->getParameter('contenu_pictures_directory') . '/' . $contenu->getImage();
                if (file_exists($imagePath)) {
                    dump($imagePath);
                    unlink($imagePath);
                }
            }

            $this->em->remove($contenu);
            $this->em->flush();

            $this->addFlash('success', 'Le contenu a été supprimé.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
        }

        return $this->render('succesModal.html.twig', [
            'titre' => 'Suppression réussie',
            'btn' => 'Retour à la liste',
            'action' => $this->generateUrl('app_contenu_index'),
        ]);
    }

    #[Route('/admin/{id}/confirm-delete', name: 'app_contenu_confirm_delete')]
    public function confirmDelete(Contenu $contenu)
    {
        return $this->render('Contenu/confirm-delete.html.twig', [
            'contenu' => $contenu
        ]);
    }

    #[Route('description/{id}', name: 'app_contenu_description', methods: ['GET'])]
    public function description(Contenu $contenu)
    {
        return $this->render('Contenu/description.html.twig', [
            'contenu' => $contenu
        ]);
    }
}
