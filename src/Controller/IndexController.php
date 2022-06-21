<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GrupoRepository;
use App\Repository\TrayectoRepository;
use App\Entity\Driver;
use App\Entity\Trayecto;
use App\Form\TrayectoFormType;

class IndexController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index(
        Request $request,
        GrupoRepository $grupoRepository, 
        TrayectoRepository $trayectoRepository): Response
    {        
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            $trayecto = [];
            $trayecto = $trayectoRepository->findBy(['driver' => $user]);

            $offset = max(0, $request->query->getInt('offset', 0));
            $paginator = $trayectoRepository->getTrayectoPaginator($user, $offset);
            
            return $this->render('index/index.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayectos' => $paginator,
                'previous' => $offset - TrayectoRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + TrayectoRepository::PAGINATOR_PER_PAGE),
            ]);
        }
    }

    /**
     * @Route("/newtime", name="app_newTime")
     */
    public function newtime(
        Request $request, 
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository)
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();

            // creamos formulario y se lo pasamos a la plantilla
            $trayecto = new Trayecto();
            $form = $this->createForm(TrayectoFormType::class, $trayecto);

            // manejamos las respuestas del formulario
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $trayecto->setDriver($user);
                $trayecto->setPassenger(true);

                $this->entityManager->persist($trayecto);
                $this->entityManager->flush();

                // Regresamos a homepage
                $offset = max(0, $request->query->getInt('offset', 0));
                $paginator = $trayectoRepository->getTrayectoPaginator($user, $offset);
                return $this->render('index/index.html.twig', [
                    'grupo' => $grupoRepository->find($grupo),
                    'trayectos' => $paginator,
                    'previous' => $offset - TrayectoRepository::PAGINATOR_PER_PAGE,
                    'next' => min(count($paginator), $offset + TrayectoRepository::PAGINATOR_PER_PAGE),
                ]);
            }
            return $this->render('index/newtime.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayecto_form' => $form->createView(),
            ]);
        }
    }
}
