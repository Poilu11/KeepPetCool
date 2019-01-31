<?php

namespace App\Form;

use App\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Présentation de votre animal',
                'attr' => [
                    'placeholder' => 'Ex : Je vous présente mon chien Medor'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'indiquer un titre !'
                    ])
                ]
            ])
            // ->add('slug')
            ->add('name', TextType::class, [
                'label' => 'Nom de votre animal de compagnie (facultatif)',
                'attr' => [
                    'placeholder' => 'Ex : Titi'
                ]
            ])
            ->add('detail', TextType::class, [
                'label' => 'Informations complémentaires sur votre animal',
                'attr' => [
                    'placeholder' => 'Merci de préciser au besoin l\'espèce ou la race de votre animal de compagnie'
                ]
            ])
            ->add('sex', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices'  => [
                    'NC' => 'NC',
                    'F' => 'F',
                    'M' => 'M'
                ],
            ])
            ->add('age', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices'  => [
                    'NC' => null,
                    '0' => 0,
                    '1' => 1,
                    '2' => 3,
                    '4' => 4,
                    '5' => 5,
                    '6' => 6,
                    '7' => 7,
                    '8' => 8,
                    '9' => 9,
                    '10' => 10,
                    '11' => 11,
                    '12' => 12,
                    '13' => 13,
                    '14' => 14,
                    '15' => 15,
                    '16' => 16,
                    '17' => 17,
                    '18' => 18,
                    '19' => 19,
                    '20' => 20                   
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Présentez votre animal de compagnie',
                'attr' => [
                    'placeholder' => 'Décrivez-nous votre animal de compagnie',
                    'rows' => 7
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez fournir une présentation de votre animal !'
                    ])
                ]
            ])
            ->add('picture1', FileType::class, [
                'label' => 'Image n°1'
            ])
            ->add('picture2', FileType::class, [
                'label' => 'Image n°2'
            ])
            ->add('picture3', FileType::class, [
                'label' => 'Image n°3'
            ])
            // ->add('isActive')
            // ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
