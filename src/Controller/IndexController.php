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
use App\Repository\DriverConsentRepository;
use App\Entity\Driver;
use App\Entity\Trayecto;
use App\Entity\Fecha;
use App\Entity\Consent;
use App\Form\TrayectoFormType;
use App\Form\FechaFormType;

#Telegram
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

#Ajax
use Symfony\Component\HttpFoundation\JsonResponse;

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
        DriverConsentRepository $driverConsentRepository): Response
    {        
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            //SUPER_ADMIN to /admin
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                return $this->redirectToRoute('plus');
            }
            $grupo = $user->getGrupo();
            $trayecto = [];
            $trayecto = $trayectoRepository->findBy(['driver' => $user]);

            $offset = max(0, $request->query->getInt('offset', 0));
            $paginator = $trayectoRepository->getTrayectoPaginator($user, $offset);
            //dump($paginator);
            $current_date = date('Y-m-d');
            $five_days = date('Y-m-d', strtotime('-5 day', strtotime($current_date)));
            $disponibles = $trayectoRepository->findAvailables([
                //'driver' => $user,
                'date_trayecto' => $five_days,
                'grupo' => $grupo,
            ]);
            //dump($disponibles);
            /* ************************* */
            /*      Consentimientos      */
            /* ************************* */
            $consent = new Consent();
            $consent = $driverConsentRepository->findConsentPending([
                'driver' => $user,
            ]);
            if(count($consent)!= 0){
                $choice = false;
            } else {
                $choice = true;
            }
            /*
            foreach($disponibles as $key => $value){
                foreach($value as $kkey => $vvalue)
                    if($kkey == "drivers"){
                        foreach($vvalue as $kkkey => $vvvalue){
                            $disponibles[$key][$kkey][$kkkey] = $driverRepository->findOneBy(["id" => $vvvalue]);
                        }
                    }
            }
            //dump($disponibles);
            */
            return $this->render('index/index.html.twig', [
                'grupo' => $grupoRepository->find($grupo),
                'trayectos' => $paginator,
                'previous' => $offset - TrayectoRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + TrayectoRepository::PAGINATOR_PER_PAGE),
                'disponibles' => $disponibles,
                'consent' => $consent
            ]);
        }
    }

    /**
     * @Route("/ajax1", name="app_ajax1")
     */
    public function ajax1(
        Request $request, 
        DriverRepository $driverRepository,
        TrayectoRepository $trayectoRepository
        ): Response
    {   
        //Return, user_id, count_passenger=false & count_passenger=true
        $request = Request::createFromGlobals();
        $userid = new Driver();
        $userid = $driverRepository->findOneBy([
            'id' => $request->request->get('user'),
        ]);
        $conductor = $trayectoRepository->cuenta($userid, false);
        $pasajero = $trayectoRepository->cuenta($userid, true);
        $response = new JsonResponse([
            'usuario' => $userid->getId(),
            'conductor' => $conductor,
            'pasajero' => $pasajero,
        ]);
        return $response;
    }

    /**
     * @Route("/newtime", name="app_newTime")
     */
    public function newtime(
        Request $request, 
        GrupoRepository $grupoRepository,
        FechaRepository $fechaRepository,
        TrayectoRepository $trayectoRepository,
        ChatterInterface $chatter): Response
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
                    'fecha' => $id_fecha,
                    'driver' => $user->getId(),
                ]);
                //dump($existe2);
                //dump($fecha);
                //dump($user);
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
                    $this->addFlash(
                        'success',
                        'Se ha grabado un nuevo horario'
                    );
                    $id_trayecto = $trayecto->getId();
                    // Telegram
                    //dump($grupo->getChatid());
                    $fecha_prevista = date_format($date_trayecto, "d/M/yy");
                    $mens = $user->getUsername() .' ha añadido un nuevo horario: ' .$fecha_prevista. " de ". date_format($time_at,"H:i") . " a " . date_format($time_to,"H:i");
                    $chatMessage = new ChatMessage($mens);
                    // Create Telegram options
                    $telegramOptions = (new TelegramOptions())
                        ->chatId($grupo->getChatid())
                        ->parseMode('MarkdownV2')
                        ->disableWebPagePreview(true)
                        ->disableNotification(true)
                        ->replyMarkup((new InlineKeyboardMarkup())
                            ->inlineKeyboard([
                                (new InlineKeyboardButton('Ir a la app'))
                                    //->url($url),
                                    ->url("https://compartecoche.davidbermudez.es/trayecto/" . $id_trayecto),
                            ])
                        );
                    // Add the custom options to the chat message and send the message
                    $chatMessage->options($telegramOptions);
                    $chatter->send($chatMessage);
                    // regresar homepage
                    //return 1;
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
        DriverRepository $driverRepository,
        $id
    ){
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
            // Ver si ya existen passenger/o drivers asignados
            foreach($otros as $track){

            }
            $estoy = false;
            $incluido_este_user = $trayectoRepository->findBy([
                'fecha' => $trayecto->getFecha(),
                'driver' => $user,
            ]);
            if($incluido_este_user){
                $estoy = true;
            }
            if(!is_null($otros[0]->isPassenger())){
                return $this->render('index/trayecto_end.html.twig', [
                    'grupo' => $grupo,
                    'otros' => $otros,
                    'estoy' => $estoy,
                    //'mayoria' => $array,
                ]);
            } else {
                //dump($otros);
                
                // ******************* //
                // ****Comparativa**** //            
                $drivers = [];
                $i = 0;
                foreach($otros as $clave){
                    array_push($drivers, $driverRepository->find($clave->getDriver()));
                    $i++;
                }
                //dump($drivers);
                // init array
                $res = [];
                foreach($drivers as $usuario1){
                    $res[$usuario1->getId()] = 0;
                }
                $drivers2 = $drivers;
                $i = 0;
                foreach($drivers as $usuario1){                
                    foreach($drivers2 as $usuario2){
                        if($usuario1 != $usuario2){
                            // realizamos comparativa de dos en dos usuarios
                            //$texto1 = $usuario1->getId() ."->". $usuario2->getId();
                            //dump($texto1);
                            $resultado1 = $trayectoRepository->compara($usuario1, $usuario2);
                            $resultado2 = $trayectoRepository->compara($usuario2, $usuario1);
                            //$texto1 = $resultado1 ." a ". $resultado2;
                            //dump($texto1);
                            if($resultado1 > $resultado2){
                                $res[$usuario1->getId()]++;
                            } elseif($resultado1 < $resultado2){
                                $res[$usuario2->getId()]++;
                            }
                        }
                    }
                    // Sacamos de drivers2 al usuario que hemos tratado (con indice $i)
                    unset($drivers2[$i]);
                    $i++;
                }        
                asort($res);
                //dump($res);
                $array = [];
                $i = 0;
                foreach($res as $key => $value){
                    $pasajero = new Driver;
                    $pasajero = $driverRepository->findOneBy(['id' => $key]);
                    $array[$value][$i] = $pasajero;         
                    $i++;
                } 
                // ******************* //

                return $this->render('index/trayecto.html.twig', [
                    'grupo' => $grupo,
                    'otros' => $otros,
                    'estoy' => $estoy,
                    'mayoria' => $array,
                ]);
            }
        }
    }

    /**
     * @Route("/passenger_accept/{id}/{driver}", name="app_passenger_accept")
     */
    public function passenger_accept(
        Request $request,
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository,
        DriverRepository $driverRepository,
        $id
    ): Response
    {        
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
        DriverRepository $driverRepository,
        $id
    ): Response
    {
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
                // Poner a null el campo passenger del resto de usuarios
                $resto = $trayectoRepository->findBy([                    
                    'fecha' => $trayecto->getFecha(),
                ]);
                foreach($resto as $pass){
                    $pass->setPassenger(null);                    
                }
                $this->entityManager->flush($resto);
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
            return $this->redirectToRoute('app_trayecto', array('id' => $id));
            /*
            return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupo,
                'otros' => $otros,
                'estoy' => $estoy,
            ]);
            */
        }        
    }


    /**
     * @Route("/delTrayecto/{id}", name="app_delTrayecto")
     */
    public function del_trayecto(
        Request $request,
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository,
        DriverRepository $driverRepository,
        $id
    ): Response
    {
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
                // Eliminamos al usuario de este trayecto
                $this->entityManager->remove($pasajero);                
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    'Se te ha eliminado de este trayecto'
                );
                // Ahora ponemos al resto de usuarios a null
                $resto = $trayectoRepository->findBy([                    
                    'fecha' => $trayecto->getFecha(),                    
                ]);
                foreach($resto as $pass){
                    $pass->setPassenger(null);             
                }
                $this->entityManager->flush($resto);
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
            return $this->redirectToRoute('homepage');
            /*return $this->render('index/trayecto.html.twig', [
                'grupo' => $grupo,
                'otros' => $otros,
                'estoy' => $estoy,
            ]);*/
        }        
    }

    /**
     * @Route("/mydriver/{id}", name="app_pongocoche")
     */
    public function pongo_coche(
        Request $request,        
        TrayectoRepository $trayectoRepository,
        $id
    ): Response
    {
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
            //$pasajero = $trayectoRepository->findOneBy([
            $pasajero = $trayectoRepository->findBy([
                //'driver' => $user,
                'fecha' => $trayecto->getFecha(),
            ]);
            $estoy = false;
            $otros = false;
            //dump($pasajero);
            foreach($pasajero as $usuario){
                //dump($usuario->getDriver());
                if ($user == $usuario->getDriver()) {
                    $estoy = true;
                    $usuario->setPassenger(false);
                } else {
                    $otros = true;
                    $usuario->setPassenger(true);
                }
            }
            if ($estoy && $otros){
                // Correcto
                //Actualizamos el estado de los miembros de este trayecto tal y como han quedado en el bucle anterior
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    'Se te ha marcado como Conductor y se ha bloqueado este trayecto'
                );
            } elseif ($estoy &&  $otros==false) {
                //$this->entityManager->rollback();
                $this->addFlash(
                    'danger',
                    'ERROR: No puedes marcarte como conductor cuando no existe nadie más en este trayecto'
                );
            } else {
                $this->entityManager->rollback();
                $this->addFlash(
                    'danger',
                    'ERROR: No te hemos encontrado en este trayecto'
                );
            }
            return $this->redirectToRoute('app_trayecto', ['id' => $id]);
        }        
    }

    /**
     * @Route("/comparativa/{id}", name="comparativa")
     */
    public function comparativa(
        Request $request,
        GrupoRepository $grupoRepository,
        TrayectoRepository $trayectoRepository,
        $id
    ): Response
    {
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
        $res = [];
        //dump($drivers);
        // init array
        $res = [];
        foreach($drivers as $usuario1){
            $res[$usuario1->getId()] = 0;
        }
        foreach($drivers as $usuario1){            
            foreach($drivers as $usuario2){
                if($usuario1 != $usuario2){
                    // realizamos comparativa entre dos usuarios
                    //$texto1 = $usuario1->getId() ."->". $usuario2->getId();
                    //dump($texto1);
                    $resultado1 = $trayectoRepository->compara($usuario1, $usuario2);
                    $resultado2 = $trayectoRepository->compara($usuario2, $usuario1);
                    //$texto1 = $resultado1 ." a ". $resultado2;
                    //dump($texto1);
                    if($resultado1 > $resultado2){
                        $res[$usuario1->getId()]++;
                    } elseif($resultado1 < $resultado2){
                        $res[$usuario2->getId()]++;
                    }
                }
            }
        }        
        asort($res);
        $array = [];
        $i = 0;
        foreach($res as $key => $value){
            $pasajero = new Driver;
            $pasajero = $driverRepository->findOneBy(['id' => $key]);
            $array[$value][$i] = $pasajero;         
            $i++;
        }        
        return $this->render('index/comparativa.html.twig', [
            'mayoria' => $array,
        ]);
    }

    /**
    * @Route("/viewlog", name ="app_log")
    */
    public function viewlog(Request $request){
        return $this->render('index/viewlog.html.twig', [
            
        ]);
    }

    /**
     * @Route("confirm/{id}", name="app_confirm_trayecto")
     */
    public function confirm(
        Request $request,
        TrayectoRepository $trayectoRepository
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
        // verificamos que el campo passenger no es null
        $error = false;
        foreach($trayectos as $track){
            if(is_null($track->isPassenger())){
                $error = true;
            }
        }
        // ponemos el campo confirm a true
        if($error==false){
            foreach($trayectos as $track){
                $track->setConfirm(true);
            }
        }
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Los datos del trayecto se han actualizado'
        );

        return $this->redirectToRoute('homepage');
    }
}
