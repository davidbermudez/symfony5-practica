<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\DriverFormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

#Telegram
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

use App\Entity\Grupo;
use App\Entity\Driver;
use App\Entity\Trayecto;
use App\Repository\GrupoRepository;
use App\Repository\DriverRepository;
use App\Repository\TrayectoRepository;

class DriverController extends AbstractController
{
    private $entityManager;    

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;        
    }

    /**
     * @Route("/driver", name="app_driver")
     */
    public function index(
        Request $request,
        string $photoDir,
        string $photoTmp,
        NotifierInterface $notifier,
        ChatterInterface $chatter,
        GrupoRepository $grupoRepository,
        DriverRepository $driverRepository
    ): Response
    {
        $user = $this->getUser();

        if ($user == null){
            
            return $this->redirectToRoute('app_login');

        } else {

            $nombreAntiguo = $user->getUsername();
            if(is_null($nombreAntiguo)) $nombreAntiguo = $user->getEmail();
            $avatarAntiguo = $user->getAvatar();
            if(is_null($avatarAntiguo)) $avatarAntiguo = 'default.png';
            $grupo = $user->getGrupo();
            //dump($grupo);
            //$datos_grupo = new Grupo();
            $datos_grupo = $grupoRepository->findOneBy(['id' => $grupo]);
            //dump($datos_grupo);
            $form = $this->createForm(DriverFormType::class, $user);
            // manejamos las respuestas del formulario
            //dump($request);

            $form->handleRequest($request);
            //dump($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $error = false;
                //dump("correcto");
                $username = $form['username']->getData();
                // ver que no esté escogido para este grupo
                $repetidos = new Driver();
                $repetidos = $driverRepository->findOneBy([
                    'username' => $username,
                    'grupo' => $grupo,
                    //'id' => $user->getId()
                ]);
                //dump($repetidos);
                
                if($repetidos){
                    $id1= $repetidos->getId();
                    $id2= $user->getId();
                    if($id1!=$id2){
                        $this->addFlash(
                            'danger',
                            'Ya existe otro usuario con el mismo nombre'
                        );
                        $error = true;
                    }                    
                } else {
                    $user->setUsername($username);
                }
                
                // verificar avatar                
                if($error == false && $avatar = $form['avatar']->getData()){
                    //foto nueva
                    $filename = bin2hex(random_bytes(6)).'.'.$avatar->guessExtension();
                    try {
                        $avatar->move($photoTmp, $filename);
                        // redimensionar a un cuadrado centrado de 100 x 100 (valor configurable en variable $dim_square)
                        $nuevaImagen = $this->redimensiona($photoTmp.$filename);
                        // convertimos a png
                        imagepng($nuevaImagen, $photoDir.$filename, 3);
                                            
                        // Actualizar Datos
                        $user->setAvatar($filename);                        
                    } catch (FileException $e) {
                        $error = true;
                        $this->addFlash(
                            'danger',
                            'Error: '.$e
                        );
                    }                
                }
                
                if($error==false){
                    $user->setPhonenumber($form['phonenumber']->getData());
                }
                
                if($error==false){
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $this->addFlash(
                        'success',
                        'Datos actualizados'
                    );

                    // Telegram
                    //dump($grupo);
                    //dump($grupo->getChatid());
                    if(!is_null($grupo->getChatid())){
                        //dump($grupo->getChatid());
                        $mens = $nombreAntiguo .' ha actualizado su perfil';
                        //$url = $this->get('router')->generate('app_change_avatar', array('avatar1' => $avatarAntiguo, 'avatar2' => $filename));
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
                                        ->url("https://google.com/"),
                                ])
                            );
                        // Add the custom options to the chat message and send the message
                        $chatMessage->options($telegramOptions);
                        $chatter->send($chatMessage);
                    }
                } else {
                    // end error
                    $this->addFlash(
                        'warning',
                        'Los datos no se han actualizado'
                    );
                }

            } elseif ($form->isSubmitted() && !$form->isValid()) {

                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
            }
            
            // Email
            /*
            $notification = (new Notification('New Invoice', ['email']))
                ->content('You got a new invoice for 15 EUR.')
                ->importance(Notification::IMPORTANCE_MEDIUM);
                $recipient = new Recipient(
                    $user->getEmail(),                    
                );
            $notifier->send($notification, $recipient);
            */

        }
        return $this->render('driver/index.html.twig', [
            'driver' => $user,
            'grupo' => $grupo,
            'form' => $form->createView(),
        ]);
        
    }

    /**
    * @Route("/changeavatar/{avatar1}/{avatar2}", name="app_change_avatar")
    */
    public function changeavatar(Request $request){
        // Verificamos que la id existe
        $array = (array) $request->attributes;        
        $avatar1 = $array["\x00*\x00parameters"]["avatar1"];
        $avatar2 = $array["\x00*\x00parameters"]["avatar2"];
        return $this->render('driver/changephoto.html.twig', [
            'avatar1' => $avatar1,
            'avatar2' => $avatar2,            
        ]);
    }


    private function redimensiona($file){
        $image_in_memory = $this->createImageFromSource($file, $file);        
        // buscar el valor mas pequeño alto o ancho        
        $x = imagesx($image_in_memory);
        $y = imagesy($image_in_memory);
        //list($x, $y) = getimagesize($image_in_memory);
        if($x > $y){
            $maxsize = $y;
        } else {
            $maxsize = $x;
        }
        // transformamos la imagen a un cuadrado
        return $this->resizeImage($image_in_memory, $x, $y, $maxsize, $maxsize);
    }

    private function createImageFromSource($source, $type)
    {
        // JPG 
        if (preg_match('/jpg|jpeg/', $type))  $data = imagecreatefromjpeg($source);
        // PNG
        if (preg_match('/png/', $type))  $data = imagecreatefrompng($source);
        // GIF
        if (preg_match('/gif/', $type))  $data = imagecreatefromgif($source);
        return $data;
    }

    private function resizeImage($original_image_data, $original_width, $original_height, $new_width, $new_height)
    {

        $dim_square = 100; // <= no tiene en cuenta $new_width
        $dst_img = ImageCreateTrueColor($dim_square, $dim_square);
        imagecolortransparent($dst_img, imagecolorallocate($dst_img, 0, 0, 0));

        // horizontal rectangle
        if ($original_width > $original_height) {
            $square = $original_height;   // $square: square side length
            $offsetX = ($original_width - $original_height) / 2;  // x offset based on the rectangle
            $offsetY = 0;                 // y offset based on the rectangle
        }
        // vertical rectangle
        elseif ($original_height > $original_width) {
            $square = $original_width;
            $offsetX = 0;
            $offsetY = ($original_height - $original_width) / 2;
        }
        // it's already a square
        else {
            $square = $original_width;
            $offsetX = $offsetY = 0;
        }
       
        imagecopyresampled(
            $dst_img,
            $original_image_data, 
            0,
            0, 
            $offsetX, 
            $offsetY, 
            $dim_square,
            $dim_square, 
            $square, 
            $square
        );

        return $dst_img;
    }

    /**
     * @Route("/user/{id}", name="app_user")
     */
    public function user_info(
        Request $request,        
        GrupoRepository $grupoRepository,
        DriverRepository $driverRepository,
        TrayectoRepository $trayectoRepository
    ): Response
    {
        // Verificamos el user
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        }
        $grupo = $user->getGrupo();
        // Verificamos que la id existe
        $array = (array) $request->attributes;        
        $id=$array["\x00*\x00parameters"]["id"];
        // Buscamos un usuario con esa id en nuestro grupo
        $usuario = new Driver();
        $usuario = $driverRepository->findOneBy([
            'id' => $id,
            'grupo' => $grupo
        ]);
        if ($usuario == null){
            throw new Exception('001: No existe ningún usuario en su grupo');
        }
        // Si se trata del mismo usuario => página de profile
        if($usuario == $user ){
            return $this->redirectToRoute('app_driver');
        }
        // Buscamos los viajes en los que usuario ha sido pasajero de user
        //$trayectoPassenger = new Trayecto();
        $trayectoPassenger = $trayectoRepository->pasajero_de($usuario, $user);
        $trayectoDriver = $trayectoRepository->pasajero_de($user, $usuario);
        //dump($trayectoPassenger);
        return $this->render('driver/user.html.twig', [
            'driver' => $usuario,
            'pasajero' => $trayectoPassenger,
            'conductor' => $trayectoDriver,
        ]);
    }
}
