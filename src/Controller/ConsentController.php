<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsentController extends AbstractController
{
    /**
     * @Route("/consent/{id}", name="app_accept_consent")
     */
    public function consentOk(): Response
    {
        return $this->render('consent/index.html.twig', [
            'controller_name' => 'ConsentController',
        ]);
    }
}
