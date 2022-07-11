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

class GrupoPlusCrudController extends GrupoCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'GRUPO(S)');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto, 
        EntityDto $entityDto, 
        FieldCollection $fields, 
        FilterCollection $filters): QueryBuilder
    {        
        $usuario = $this->getUser()->getId();        
        $result = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        //$result
        //    ->leftJoin('entity.driver', 'driver')
        //    ->andWhere('driver.id = :usuario')
        //    ->setParameter('usuario', $usuario);
        return $result;
    }
    
    public function configureActions(Actions $actions): Actions
    {    
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function(Action $action) {
                return $action->setLabel("Añadir Grupo");
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function(Action $action){
                return $action->setLabel("Crear Grupo");            
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function(Action $action){
                return $action->setLabel("Crear Nuevo y Añadir Otro");            
            })
            //->remove(Crud::PAGE_DETAIL, Action::DELETE)
        ;        
    }
    
}