<?php

namespace App\Controller\Admin;

use App\Entity\Grupo;
use App\Entity\Trayecto;
use App\Entity\Fecha;
use App\Repository\GrupoRepository;
use App\Repository\TrayectoRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use Symfony\Component\Form\FormType;

//use App\Controller\Admin\ConfirmField;

class FechaCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {        
        return (Fecha::class);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date_trayecto')->setFormat('dd/MM/yyyy'),
            TimeField::new('time_at')->setFormat('HH:mm'),
            TimeField::new('time_to')->setFormat('HH:mm'),
            //ConfirmField::new('confirm', 'Confirmado')

            //AssociationField::new('driver'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityPermission('')
            ->setDefaultSort([
                'date_trayecto' => 'DESC',
                'time_at' => 'DESC'
            ])
            // set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
            //->renderContentMaximized()

            // set this option if you prefer the sidebar (which contains the main menu)
            // to be displayed as a narrow column instead of the default expanded design
            //->renderSidebarMinimized()
            
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            //->add('admin')
            //->add('driver')
            //->add('state')
        ;
    }

        
}
