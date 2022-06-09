<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\HttpFoundation\File\File;


class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $avatar = ImageField::new('photoFilename')->setBasePath('images/uploads/')->setLabel('Photo');
        $avatarTextFile = TextField::new('photoFilename');
        {
            yield TextField::new('author', 'Autor');
            yield TextEditorField::new('text', 'Comentario');
            yield EmailField::new('email', 'Email');
            yield DateTimeField::new('createdAt', 'Creado')
                ->setFormat('dd/MM/y HH:mm:ss')
                ->setSortable(true)
                ->setFormTypeOption('disabled', 'disabled');
        
            if (Crud::PAGE_INDEX === $pageName) {
                yield ImageField::new('photoFilename')->setBasePath('images/uploads/')->setLabel('Photo');                
            } elseif (Crud::PAGE_EDIT === $pageName) {
                yield TextField::new('photoFilename')->setLabel('Photo');
            }
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('conference')
            //->add('state')
        ;
    }
    
}
