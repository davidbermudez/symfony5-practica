<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class FechaFiltradoCrudController extends FechaCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Trayectos');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto, 
        EntityDto $entityDto, 
        FieldCollection $fields, 
        FilterCollection $filters): QueryBuilder
    {        
        $usuario = $this->getUser();
        //dump($usuario);
        $result = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $result            
            ->andWhere('entity.grupo = :grupo')
            ->setParameter('grupo', $usuario->getGrupo())
            //->orderBy('entity.date_trayecto', 'entity.time_at')
        ;
        return $result;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Los ROLE_ADMIN no pueden crear, ni eliminar ningún grupo
        return $actions
            //->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            //->remove(Crud::PAGE_DETAIL, Action::DELETE)            
        ;
    }

}