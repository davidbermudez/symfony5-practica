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
            dump($request);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if($avatar = $form['avatar']->getData()){
                    //foto nueva
                    $filename = bin2hex(random_bytes(6)).'.'.$avatar->guessExtension();
                    try {
                        $avatar->move($photoDir, $filename);
                        dump($photoDir);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                        dump($e);
                    }
                    $user->setAvatar($filename);
                }
                // Actualizar Datos
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    'Datos actualizados'
                );
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
}
