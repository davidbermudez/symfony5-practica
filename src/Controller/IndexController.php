<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\Query\ResultSetMapping;
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
            // ADMIN to /admin            
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin');
            }
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
                // verificar que no exista un trayecto igual ya guardado por este usuario
                //dump($form);                
                $fecha = $form['date_trayecto']->getData();
                $time_at = $form['time_at']->getData();
                $time_to = $form['time_to']->getData();
                //dump($fecha);
                //dump($time_at);
                //dump($time_to);
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
                        'ERROR: Ya has grabado un trayecto guardado para este horario'
                    );
                } else {
                    // Grabamos un nuevo trayecto
                    $trayecto->setDriver($user);
                    $trayecto->setPassenger(null);

                    $this->entityManager->persist($trayecto);
                    $this->entityManager->flush();

                    // verificamos si ya hay grabado un trayecto igual (por otro usuario)
                    $buscando_iguales = [];//new Trayecto();
                    $buscando_iguales = $this->buscaTrayectos([
                        //'driver' => $user->getId(),
                        'driver' => $user,
                        //'date_trayecto' => $form['date_trayecto']->getData()->format('Y-m-d'),
                        'date_trayecto' => $form['date_trayecto']->getData(),
                        'time_at' =>  $form['time_at']->getData()->format('H:i:s'),
                        'time_to' => $form['time_to']->getData()->format('H:i:s'),
                    ]);
                    dump($buscando_iguales);
                    if($buscando_iguales){
                        //
                        $this->addFlash(
                            'success',
                            'ATENCIÓN: ¡Ya existe un trayecto guardado para este horario!'
                        );
                    }
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

    public function buscaTrayectos($value): array
    {
        $rsm = new ResultSetMapping();
        $sql = "SELECT t.* FROM trayecto t INNER JOIN driver d ON t.driver_id = d.id WHERE t.driver_id != :val0 AND t.date_trayecto = :val1 AND t.time_at = :val2 AND t.time_to = :val3 AND d.grupo_id = (SELECT e.grupo_id FROM driver e WHERE e.id = :val0)";
        $sql = "SELECT t.* FROM trayecto t WHERE 1";
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        //$query->setParameters([
            //'val0' => $value['driver'],
            //'val1' => $value['date_trayecto'],
            //'val2' => $value['time_at'],
            //'val3' => $value['time_to'],
        //]);

        $trayectos = $query->getResult();
        dump($query);
        dump($trayectos);
        return $trayectos;
    }
}
