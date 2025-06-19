<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app_home')]
    public function index()
    {
        return $this->render('/base.html.twig');
    }

//    TODO : Crée une page sur la politique de confidentialité
    #[Route(path: '/politique-confidencialite', name: 'app_politique_confidencialite')]
    public function privacyPolicy()
    {
        return $this->render('Security/politiqueConfidentialite.html.twig');
    }

//    TODO : Crée une page sur les mention légale
//    #[Route('/mention-legal', name: 'app_mention_legal')]
//    public function legalNotice(): Response
//    {
//        return $this->render('Security/mentionLegal.html.twig');
//    }

}