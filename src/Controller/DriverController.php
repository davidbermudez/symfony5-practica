<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\DriverFormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
        string $photoDir
    ): Response
    {
        $user = $this->getUser();
        if ($user == null){
            return $this->redirectToRoute('app_login');
        } else {
            $grupo = $user->getGrupo();
            $form = $this->createForm(DriverFormType::class, $user);
            // manejamos las respuestas del formulario
            //dump($request);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if($avatar = $form['avatar']->getData()){
                    //foto nueva
                    $filename = bin2hex(random_bytes(6)).'.'.$avatar->guessExtension();
                    try {
                        $avatar->move($photoDir, $filename);                        
                        dump($photoDir);
                        dump($filename);
                        $nuevaImagen = $this->redimensiona($photoDir.$filename);
                        $user->setAvatar($filename);
                        //dump($photoDir);
                        // Actualizar Datos
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                        $this->addFlash(
                            'success',
                            'Datos actualizados'
                        );
                    } catch (FileException $e) {
                        $this->addFlash(
                            'danger',
                            'Error: '.$e
                        );
                    }                    
                }                
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
            }
            return $this->render('driver/index.html.twig', [
                'driver' => $user,
                'grupo' => $grupo,
                'form' => $form->createView(),
            ]);
        }
    }

    private function redimensiona($file){
        $file = $this->createImageFromSource($file, $file);
        $dump($file);
    }

    private function createImageFromSource($source, $type){
        dump($source);
        // JPG 
        if (preg_match('/jpg|jpeg/', $type))  $data = imagecreatefromjpeg($source);
        // PNG
        if (preg_match('/png/', $type))  $data = imagecreatefrompng($source);
        // GIF
        if (preg_match('/gif/', $type))  $data = imagecreatefromgif($source);
        return $data;
    }
    private function resizeImage($original_image_data, $original_width, $original_height, $new_width, $new_height){
        $dst_img = ImageCreateTrueColor($new_width, $new_height);
        imagecolortransparent($dst_img, imagecolorallocate($dst_img, 0, 0, 0));
        imagecopyresampled($dst_img, $original_image_data, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        return $dst_img;
    }
}
