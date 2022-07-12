<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class ConfirmField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null)
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(RadioType::class)
            ->addCssClass('field-boolean')
            ->setDefaultColumns('col-md-4 col-xxl-3')
        ;
    }

    /*public static function getAsDto()
    {

    }
*/
}