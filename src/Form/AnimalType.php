<?php

namespace App\Form;

use App\Entity\Animal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
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
                'label' => 'Titre de présentation de votre animal (*)',
                'attr' => [
                    'placeholder' => 'Présentation de Titi, mon petit pitbull chéri'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'indiquer un titre !'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 120,
                        'minMessage' => 'Minimum {{ limit }} caractères',
                        'maxMessage' => 'Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            // ->add('slug')
            ->add('name', TextType::class, [
                'label' => 'Nom de votre animal de compagnie (facultatif)',
                'attr' => [
                    'placeholder' => 'Titi'
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'Minimum {{ limit }} caractères',
                        'maxMessage' => 'Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('detail', TextType::class, [
                'label' => 'Informations complémentaires sur votre animal (facultatif)',
                'attr' => [
                    'placeholder' => 'Merci de préciser au besoin l\'espèce ou la race de votre animal de compagnie'
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 500,
                        'minMessage' => 'Minimum {{ limit }} caractères',
                        'maxMessage' => 'Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sexe de votre animal ? (facultatif)',
                'expanded' => false,
                'multiple' => false,
                'choices'  => [
                    'NC' => 'NC',
                    'F' => 'F',
                    'M' => 'M'
                ],
                'attr' => [
                    'style' => 'width: 200px'
                ],
            ])
            ->add('age', ChoiceType::class, [
                'label' => 'Quel âge a votre animal de compagnie ? (facultatif)',
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                        'style' => 'width: 200px'
                ],
                'choices'  => [
                    'NC' => null,
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
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
                    '20' => 20,
                    '21' => 21     
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Présentez votre animal de compagnie (*)',
                'attr' => [
                    'placeholder' => 'Décrivez-nous votre animal de compagnie, son caractère, ses habitudes, ses goûts, etc.',
                    'rows' => 7
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez fournir une présentation de votre animal !'
                    ]),
                ]
            ])
            ->add('picture1', FileType::class, [
                'label' => 'Image n°1 de votre animal (facultatif mais recommandée)'
            ])
            ->add('picture2', FileType::class, [
                'label' => 'Image n°2 de votre animal (facultatif)'                
            ])
            ->add('picture3', FileType::class, [
                'label' => 'Image n°3 de votre animal (facultatif)'               
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
