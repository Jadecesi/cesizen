<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class DiagnosticController extends AbstractController
{
   #[Route(path: '/diagnostic', name: 'app_diagnostic')]
    public function diagnostic()
    {
         return $this->render('diagnostic.html.twig');
    }

}