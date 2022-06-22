<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
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
            // intento de enviar valores por defecto TO-DO
            // $form->setData('hola');

            // manejamos las respuestas del formulario
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /* ******************************************  */
                // verificar que no exista un trayecto igual
                //dump($form);                
                $fecha = $form['date_trayecto']->getData();
                $time_at = $form['time_at']->getData();
                $time_to = $form['time_to']->getData();
                dump($fecha);
                dump($time_at);
                dump($time_to);
                $verificando_trayecto = [];
                $verificando_trayecto = $trayectoRepository->findBy([
                    'driver' => $user,
                    'date_trayecto' => $fecha,
                    'time_at' => $time_at,
                    'time_to' => $time_to,
                ]);
                dump($verificando_trayecto);
                /* ******************************************  */
                if($verificando_trayecto){
                    //
                    $this->addFlash(
                        'danger',
                        'Ya existe un trayecto guardado para este horario'
                    );
                } else {
                
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
            }
            return $this->render('index/newtime.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayecto_form' => $form->createView(),
            ]);
        }
    }
}
