<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\DriverRepository;

// Paginator
use Symfony\Component\HttpFoundation\Request;

class GrupoController extends AbstractController
{
    /**
     * @Route("/grupo", name="app_grupo")
     */
    public function index(
        Request $request,
        DriverRepository $driverRepository): Response
    {
        $user = $this->getUser();

        // Paginador
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $driverRepository->getDriverPaginator($user, $offset);        
        return $this->render('grupo/index.html.twig', [
            'grupo' => $grupo = $user->getGrupo(),
            //'usuarios' => $driverRepository->findBy(['grupo' => $grupo]),
            'usuarios' => $paginator,
            'previous' => $offset - DriverRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + DriverRepository::PAGINATOR_PER_PAGE),

        ]);
    }
}
