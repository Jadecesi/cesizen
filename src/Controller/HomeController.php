<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app_home')]
    public function index()
    {
        return $this->render('/base.html.twig');
    }

//    TODO : Crée une page sur la politique de confidentialité
//    #[Route(path: '/privacy-policy', name: 'app_about')]
//    public function privacyPolicy()
//    {
//        return $this->render('privacy/privacy-policy.html.twig');
//    }

//    TODO : Crée une page sur les mention légale
//    #[Route('/legal-notice', name: 'app_legal_notice')]
//    public function legalNotice(): Response
//    {
//        return $this->render('legal/legal-notice.html.twig');
//    }

}