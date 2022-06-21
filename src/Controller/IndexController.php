<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\GrupoRepository;
use App\Repository\TrayectoRepository;
use App\Entity\Driver;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(GrupoRepository $grupoRepository, TrayectoRepository $trayectoRepository): Response
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            return $this->render('index/index.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayectos' => $trayectoRepository->findBy(['driver' => $user]),
            ]);
        }

    }

    /**
     * @Route("/newtime", name="app_newTime")
     */
    public function newtime()
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            return $this->render('index/newtime.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
            ]);
        }
    }
}
