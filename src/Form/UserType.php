<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
// use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Identifiant',
                'attr' => [
                    'placeholder' => 'Exemple : Richard75'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un identifiant'
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 25,
                        'minMessage' => 'Identifiant trop court ! Minimum {{ limit }} caractères',
                        'maxMessage' => 'Identifiant trop long ! Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('password')
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'Exemple : example@gmail.com'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez fournir une adresse email'
                    ]),
                    new Email([
                        'mode' => 'loose',
                        'message' => 'L\'adresse email saisie n\'est pas valide'
                    ])
                ]
            ])
            // ->add('isActive')
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Richard'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un prénom'
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Dupond'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un nom'
                    ])
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => '117 boulevard des Champs Elysées'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un nom'
                    ])
                ]
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code Postal',
                'attr' => [
                    'placeholder' => '75000'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un code postal'
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 10,
                        'minMessage' => 'Code postal trop court ! Minimum {{ limit }} caractères',
                        'maxMessage' => 'Code postal trop long ! Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Paris'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner une ville'
                    ])
                ]
            ])
            // ->add('longitude')
            // ->add('latitude')
            // ->add('pathAvatar') // TODO plus tard
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro téléphone fixe',
                'required' => false,
                'attr' => [
                    'placeholder' => '01-20-30-40-50'
                ],
            ])
            ->add('cellNumber', TextType::class, [
                'label' => 'Numéro téléphone mobile',
                'required' => false,
                'attr' => [
                    'placeholder' => '07-20-30-40-50'
                ],
            ])
            // ->add('pathCertificat')
            // ->add('isValidated')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('connectedAt')
            ->add('type', ChoiceType::class, [
                'label' => 'Vous êtes...',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Petsitter' => 'petsitter',
                    'Propriétaire d\'un animal de compagnie' => 'owner'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner un type'
                    ])
                ]
                // 'constraints' => [
                //     new Choice([
                //         'min' => 1,
                //         'max' => 1,
                //         'minMessage' => 'Vous devez sélectionner au moins {{ limit }} type',
                //         'maxMessage' => 'Vous devez sélectionner au plus {{ limit }} type'
                //     ])
                // ]

            ])
            // ->add('role')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
