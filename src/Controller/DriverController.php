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
        string $photoDir,
        string $photoTmp
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
                        $avatar->move($photoTmp, $filename);
                        // redimensionar a un cuadrado centrado de 100 x 100 (valor configurable en variable $dim_square)
                        $nuevaImagen = $this->redimensiona($photoTmp.$filename);
                        // convertimos a png
                        imagepng($nuevaImagen, $photoDir.$filename, 3);
                                            
                        // Actualizar Datos
                        $user->setAvatar($filename);
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
}
