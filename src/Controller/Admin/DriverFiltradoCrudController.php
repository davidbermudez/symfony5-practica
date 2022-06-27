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

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class DriverFiltradoCrudController extends DriverCrudController
{
    

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Usuarios');
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
            ->setParameter('grupo', $usuario->getGrupo());
        return $result;
    }

    public function configureFields(string $pageName): iterable
    {        
        return [
            //IdField::new('id'),
            EmailField::new('email'),
            HiddenField::new('password'),
            AssociationField::new('grupo')->setFormTypeOption('disabled','disabled'),
            //TextEditorField::new('description'),
        ];
    }
   
/*
    public function configureActions(Actions $actions): Actions
    {
        
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;
        
    }
*/
}