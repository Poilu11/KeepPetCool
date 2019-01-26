<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Species;
use App\Entity\Presentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PresentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('body')
            // ->add('isActive')
            // ->add('slug')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('user')
            ->add('species', EntityType::class, [
                'label' => 'Catégorie d\espèces pour le petsitting',
                'class' => Species::class,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('services', EntityType::class, [
                'label' => 'Prestations proposées',
                'class' => Service::class,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Presentation::class,
        ]);
    }
}
