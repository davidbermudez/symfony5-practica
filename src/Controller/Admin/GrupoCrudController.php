<?php

namespace App\Controller\Admin;

use App\Entity\Grupo;
use App\Entity\Admin;
use App\Repository\GrupoRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use Symfony\Component\Form\FormType;

class GrupoCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {        
        return (Grupo::class);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('caption'),
            TextField::new('description'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityPermission('')
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
