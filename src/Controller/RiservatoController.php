<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RiservatoController extends AbstractController
{
    #[Route('/riservato', name: 'riservato')]
    public function index(): Response
    {
        return $this->render('riservato/index.html.twig', [
            'controller_name' => 'RiservatoController',
        ]);
    }
}
