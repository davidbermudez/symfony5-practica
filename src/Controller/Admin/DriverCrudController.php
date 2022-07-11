<?php

namespace App\Controller\Admin;

use App\Entity\Driver;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class DriverCrudController extends AbstractCrudController
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public static function getEntityFqcn(): string
    {
        return Driver::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            EmailField::new('email'),
            //TextEditorField::new('description'),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        // usuario admin
        $usuario = $this->getUser();
        // usuario driver
        $driver = new Driver();        
        // mismo grupo que admin
        $driver->setGrupo($usuario->getGrupo());
        // valores por defecto
        $roles = array("ROLE_USER", "ROLE_DRIVER");
        $driver->setRoles($roles);
        $driver->setAvatar('default.png');
        // contraseña hasheada
        $passPlain = date('Ymdgis');
        //$driver->setPassword($this->hashPassDriver($driver, $encoder));
        $driver->setPassword($this->encoder->encodePassword($driver, $passPlain));
        
        return $driver;
    }

}
