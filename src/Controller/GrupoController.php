<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\DriverRepository;

class GrupoController extends AbstractController
{
    /**
     * @Route("/grupo", name="app_grupo")
     */
    public function index(DriverRepository $driverRepository): Response
    {
        $user = $this->getUser();
        return $this->render('grupo/index.html.twig', [
            'grupo' => $grupo = $user->getGrupo(),
            'usuarios' => $driverRepository->findBy(['grupo' => $grupo]),
        ]);
    }
}
