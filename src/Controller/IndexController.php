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
use App\Repository\FechaRepository;
use App\Entity\Driver;
use App\Entity\Trayecto;
use App\Entity\Fecha;
use App\Form\TrayectoFormType;
use App\Form\FechaFormType;

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
        TrayectoRepository $trayectoRepository,
        DriverRepository $driverRepository,
        FechaRepository $fechaRepository): Response
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
            //dump($paginator);
            $disponibles = $trayectoRepository->findAvailables([
                'driver' => $user,
                'date_trayecto' => date('Y-m-d'),                            
                'grupo' => $grupo,
            ]);
            dump($disponibles);
            /*
            foreach($disponibles as $key => $value){
                foreach($value as $kkey => $vvalue)                    
                    if($kkey == "drivers"){
                        foreach($vvalue as $kkkey => $vvvalue){
                            $disponibles[$key][$kkey][$kkkey] = $driverRepository->findOneBy(["id" => $vvvalue]);
                        }
                    }
            }
            dump($disponibles);
            */
            return $this->render('index/index.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayectos' => $paginator,
                'previous' => $offset - FechaRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + FechaRepository::PAGINATOR_PER_PAGE),
                'disponibles' => $disponibles,
            ]);
        }
    }

    /**
     * @Route("/newtime", name="app_newTime")
     */
    public function newtime(
        Request $request, 
        GrupoRepository $grupoRepository,
        FechaRepository $fechaRepository,
        TrayectoRepository $trayectoRepository): Response
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            // creamos formulario Fechas y se lo pasamos a la plantilla
            $fecha = new Fecha();
            $form = $this->createForm(FechaFormType::class, $fecha);
            // manejamos las respuestas del formulario
            $form->handleRequest($request);
            //dump($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $date_trayecto = $form['date_trayecto'];
                $time_at = $form['time_at'];
                $time_to = $form['time_to'];
                $date_trayecto = $form['date_trayecto']->getData();
                $time_at = $form['time_at']->getData();
                $time_to = $form['time_to']->getData();
                // Verificar si ya existe un registro "Fecha" para este Horario
                $existe = $fechaRepository->findBy([
                    'date_trayecto' => $date_trayecto,
                    'time_at' => $time_at,
                    'time_to' => $time_to,
                ]);                
                if($existe){                    
                    $id_fecha = $existe[0]->getId();
                } else {
                    // Creamos un nuevo registro Fecha
                    $fecha->setGrupo($grupo);
                    $this->entityManager->persist($fecha);
                    $this->entityManager->flush();
                    $id_fecha = $fecha->getId();
                    $existe = [$fecha];
                }
                // Verificar si ya existe un registro trayecto para este id de Fecha y Usuario
                $trayecto = new Trayecto();
                $existe2 = $trayectoRepository->findBy([
                    'fecha' => $fecha,
                    'driver' => $user,
                ]);
                if($existe2){
                    $this->addFlash(
                        'danger',
                        'ERROR: Ya has grabado un trayecto guardado para este horario'
                    );
                } else {
                    // Grabamos un nuevo trayecto
                    $trayecto->setDriver($user);
                    $trayecto->setFecha($existe[0]);
                    $trayecto->setPassenger(null);
                    $this->entityManager->persist($trayecto);
                    $this->entityManager->flush();
                    // regresar homepage
                    return $this->redirectToRoute('homepage');
                }
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
            }
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
        TrayectoRepository $trayectoRepository,
        FechaRepository $fechaRepository
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
            // Enviamos el trayecto de la id y además todos aquellos con la misma fecha (Fecha, quiere decir fecha, hora inicial y fin y grupo).
            $grupo = $user->getGrupo();            
            //dump($trayecto);
            if($trayecto->getDriver()->getGrupo() != $grupo){
                throw new Exception('002: No existe el trayecto indicado en su grupo');
            }
            $otros = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
            ]);
            dump($otros);
            $estoy = false;
            $incluido_este_user = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
                'driver' => $user,
            ]);
            if($incluido_este_user){
                $estoy = true;
            }
            return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupo,
                'otros' => $otros,
                'estoy' => $estoy,
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
        //dump($driver);
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


    /**
     * @Route("/addTrayecto/{id}", name="app_addTrayecto")
     */
    public function add_trayecto(
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
        // Verificamos el user
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            // Verificamos que el usuario no tiene grabado un trayecto en la misma Fecha que $id        
            $pasajero = $trayectoRepository->findBy([
                'driver' => $user,
                'fecha' => $trayecto->getFecha(),
            ]);
            if ($pasajero == null){
                // Correcto
                // Grabamos un nuevo trayecto para este usuario
                $myTrayecto = new Trayecto();
                $myTrayecto->setDriver($user);
                $myTrayecto->setFecha($trayecto->getFecha());
                $this->entityManager->persist($myTrayecto);
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    'Se te ha añadido al trayecto con éxito'
                );
            } else {
                $this->addFlash(
                    'danger',
                    'ERROR: Ya has grabado un trayecto para este mismo horario'
                );
            }
            $otros = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
            ]);            
            $estoy = false;
            $incluido_este_user = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
                'driver' => $user,
            ]);
            if($incluido_este_user){
                $estoy = true;
            }
            return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupo,
                'otros' => $otros,
                'estoy' => $estoy,
            ]);
        }        
    }


    /**
     * @Route("/delTrayecto/{id}", name="app_delTrayecto")
     */
    public function del_trayecto(
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
        // Verificamos el user
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            // Verificamos que el usuario tiene grabado un trayecto en la misma Fecha que $id
            $pasajero = $trayectoRepository->findOneBy([
                'driver' => $user,
                'fecha' => $trayecto->getFecha(),
            ]);
            if ($pasajero){
                // Correcto
                // Eliminamos este trayecto al usuario
                $this->entityManager->remove($pasajero);                
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    'Se te ha eliminado de este trayecto'
                );
            } else {
                $this->addFlash(
                    'danger',
                    'ERROR: No te hemos encontrado en este trayecto'
                );
            }
            $otros = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
            ]);            
            $estoy = false;
            $incluido_este_user = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
                'driver' => $user,
            ]);
            if($incluido_este_user){
                $estoy = true;
            }
            return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupo,
                'otros' => $otros,
                'estoy' => $estoy,
            ]);
        }        
    }


    /**
     * @Route("/comparativa/{id}", name="comparativa")
     */
    public function comparativa(
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
            return $this->render('index/comparativa_null.html.twig', []);
        }
        // Cargamos todos los trayectos con la misma fecha y grupo
        $trayectos = $trayectoRepository->findBy([
            'fecha' => $trayecto->getFecha(),
        ]);
        $drivers = [];
        $i = 0;
        foreach($trayectos as $clave){
            array_push($drivers, $clave->getDriver());
            $i++;
        }
        $res=[];
        foreach($drivers as $usuario1){            
            foreach($drivers as $usuario2){
                if($usuario1 != $usuario2){
                    // realizamos comparativa entre dos usuarios
                    $resultado1 = $trayectoRepository->compara($usuario1, $usuario2);
                    $resultado2 = $trayectoRepository->compara($usuario2, $usuario1);
                    if($resultado1 > $resultado2){
                        $res[$usuario1]++;
                    } elseif($resultado1 < $resultado2){
                        $res[$usuario2]++;
                    }
                }
            }
        }
        dump($res);
        $return = '';
        return new Response($valor);
    }

}
