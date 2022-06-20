<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');            
        } else {
            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
            ]);
        }

    }
}
