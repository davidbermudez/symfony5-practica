<?php

namespace App\Form;

use App\Entity\Trayecto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TrayectoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fecha_actual = date("d-m-Y");
        $builder
            ->add('date_trayecto', null, [
                'label' => 'Fecha',
                'data' => new \DateTime(),
                'empty_data' => $fecha_actual,
                'widget' => 'single_text',
                ])
            ->add('time_at', null,
            [
                'widget' => 'single_text',
            ])
            ->add('time_to', null,
            [
                'widget' => 'single_text',
            ])
            //->add('passenger')
            //->add('driver')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        
        $resolver->setDefaults([
            'data_class' => Trayecto::class,
        ]);
    }
}
