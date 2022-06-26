<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GrupoRepository;
use App\Repository\TrayectoRepository;
use App\Repository\DriverRepository;
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
                'disponibles' => $trayectoRepository->findAvailables([
                    'driver' => $user,
                    'date_trayecto' => date('Y-m-d'),                            
                    'grupo' => $grupo,
                ])
            ]);
        }
    }

    /**
     * @Route("/newtime", name="app_newTime")
     */
    public function newtime(
        Request $request, 
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository): Response
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
            //dump($request);
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
                    $buscando_iguales = []; //new Trayecto();
                    $buscando_iguales = $trayectoRepository->findTrayectos2([
                        //'driver' => $user->getId(),
                        'driver' => $user,
                        //'date_trayecto' => $form['date_trayecto']->getData()->format('Y-m-d'),
                        'date_trayecto' => $fecha,
                        'time_at' => $form['time_at']->getData()->format('H:i:s'),
                        'time_to' => $form['time_to']->getData()->format('H:i:s'),
                        'grupo' => $grupo,
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
                        'disponibles' => $trayectoRepository->findAvailables([
                            'driver' => $user,
                            'date_trayecto' => date('Y-m-d'),
                            'grupo' => $grupo,
                        ])
                    ]);
                }
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
            }
            //dump($form);
            return $this->render('index/newtime.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'form' => $form->createView(),                
            ]);
        }
    }
    
    /**
     * @Route("/trayecto/{id}", name="app_trayecto")
     */
    public function trayecto(
        Request $request,
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository
    ){
        // Verificamos que la id existe
        $array = (array) $request->attributes;        
        $id=$array["\x00*\x00parameters"]["id"];        
        $trayecto = new Trayecto;
        $trayecto = $trayectoRepository->findOneBy(['id' => $id]);        
        if ($trayecto == null){
            throw new Exception('001: No existe el trayecto indicado en su grupo');
        }
        // Verificamos el user
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            // Enviamos el trayecto de la id y además el resto de trayectos que coincidan en fecha, hora y grupo
            $grupo = $user->getGrupo();
            //dump($trayecto->getDriver()->getGrupo());
            //dump($trayecto);
            if($trayecto->getDriver()->getGrupo() != $grupo){
                throw new Exception('002: No existe el trayecto indicado en su grupo');
            }
            $otros = new Trayecto();
            $otros = $trayectoRepository->findTrayectos3([
                'driver' => $user,
                'date_trayecto' => $trayecto->getDateTrayecto()->format('Y-m-d'),
                'time_at' => $trayecto->getTimeAt()->format('H:i:s'),
                'time_to' => $trayecto->getTimeTo()->format('H:i:s'),
                'grupo' => $grupo,
                'exclude' => $id
            ]);
            dump($otros);
            return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'datos_trayecto' => $trayecto,
                'otros' => $otros,
            ]);
        }
    }

    /**
     * @Route("/passenger_accept/{id}/{driver}", name="app_passenger_accept")
     */
    public function passenger_accept(
        Request $request,
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository,
        DriverRepository $driverRepository
    ): Response
    {        
        $array = (array) $request->attributes;
        // Verificamos que la id existe
        $id = $array["\x00*\x00parameters"]["id"];        
        $trayecto = new Trayecto;
        $trayecto = $trayectoRepository->findOneBy(['id' => $id]);        
        if ($trayecto == null){
            throw new Exception('001: No existe el trayecto indicado en su grupo');
        }
        // Verificamos que el pasajero existe
        $driver = $array["\x00*\x00parameters"]["driver"];
        dump($driver);
        $pasajero = new Driver;
        $pasajero = $driverRepository->findOneBy(['id' => $driver]);
        if ($pasajero == null){
            throw new Exception('001: No existe el pasajero indicado en su grupo');
        }
        // Verificamos el user
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();            
            if($trayecto->getDriver()->getGrupo() != $grupo){
                throw new Exception('002: No existe el trayecto indicado en su grupo');
            }
            // Modificamos base de datos            
            // Buscamos un trayecto guardado por este usuario con lo mismos datos de fecha y horas
            // Si existe, marcamos el campo passenger como false
            // Si no existe, lo creamos y marcamos el campo passenger como false
            $myTrayecto = new Trayecto();
            $myTrayecto = $trayectoRepository->findOneBy([
                'driver' => $user,
                'date_trayecto' => $trayecto->getDateTrayecto(),
                'time_at' => $trayecto->getTimeAt(),
                'time_to' => $trayecto->getTimeTo(),
            ]);
            if ($myTrayecto == null){
                // Crearlo
                $myTrayecto = new Trayecto();
                $myTrayecto->setDriver($user);
                $myTrayecto->setDateTrayecto($trayecto->getDateTrayecto());
                $myTrayecto->setTimeAt($trayecto->getTimeAt());
                $myTrayecto->setTimeTo($trayecto->getTimeTo());
                $myTrayecto->setPassenger(false);
            } else {
                $myTrayecto->setPassenger(false);
            }
            $this->entityManager->persist($myTrayecto);
            $this->entityManager->flush();
            // Y cambiar el trayecto actual para que el usuario driver passenger sea true
            $trayecto->setPassenger(true);
            $this->entityManager->persist($trayecto);
            $this->entityManager->flush();
            // Notificar al usuario driver TO-DO
        }
        return $this->render('index/passenger_accept.html.twig', [
            'grupo' => $grupoRepository->find($grupo),
            'trayecto' => $trayectoRepository->find($id),
        ]);
    }
}
