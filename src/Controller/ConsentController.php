<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\Consent;
use App\Entity\DriverConsent;
use App\Repository\DriverConsentRepository;
use App\Repository\ConsentRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConsentController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/consent/{id}", name="app_accept_consent")
     */
    public function consentOk(
        Request $request,
        DriverConsentRepository $driverConsentRepository,
        ConsentRepository $consentRepository,
        $id)
    {
        $user = $this->getUser();    
        $consent = $consentRepository->findOneBy([
            'id' => $id,
        ]);
        if(is_null($consent)){
            return false;
        }
        $driverconsent = new DriverConsent();
        $driverconsent = $driverConsentRepository->findBy([
            'consent' => $consent,
            'driver' => $user,
        ]);
        //dump($driverconsent);
        $fecha_actual = date("d-m-Y H:i");
        if($driverconsent){
            // existe, update choice to true
            $driverconsent->setDateConsent($fecha_actual);
            $driverconsent->setChoice(true);
        } else {
            // no existe, crear
            $driverconsent = new DriverConsent();
            $driverconsent->setDateConsent(\DateTime::createFromFormat("d-m-Y H:i", $fecha_actual));
            $driverconsent->setDriver($user);
            $driverconsent->setConsent($consent);
            $driverconsent->setChoice(true);
            $this->entityManager->persist($driverconsent);
        }
        //dump($driverconsent);
        $this->entityManager->flush();
        return $this->redirectToRoute('homepage');
    }
}
