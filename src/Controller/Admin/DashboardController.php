<?php

namespace App\Controller\Admin;

use App\Entity\Driver;
use App\Entity\Grupo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
//use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Configure access for id admin
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        //return parent::index();
        $routeBuilder = $this->get(AdminUrlGenerator::class);        
        return $this->redirect($routeBuilder->setController(DriverFiltradoCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        //$name = $this->getUser()->getUsername();
        return Dashboard::new()
            ->setTitle('Compartecoche')
            //->renderContentMaximized()            
        ;
    }

    public function configureMenuItems(): iterable
    {
        $user = $this->getUser()->getId();
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Users', 'fa fa-user');
        yield MenuItem::linkToCrud('Grupo', 'fas fa-list', Grupo::class)
            ->setController(GrupoFiltradoCrudController::class);
        yield MenuItem::linkToCrud($this->getUser()->getGrupo()->getCaption(), 'fas fa-users', Driver::class)
            ->setController(DriverFiltradoCrudController::class);
        yield MenuItem::linkToCrud('Trayectos', 'fas fa-users', Trayecto::class)
            ->setController(FechaFiltradoCrudController::class);
        yield MenuItem::linkToRoute('Ir a la App', 'fas fa-home', 'homepage');
    }

    
}
