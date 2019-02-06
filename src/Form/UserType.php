<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un listener
        $listener = function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();
            
            // Dans le cas d'un signup
            if(is_null($user->getId()))
            {
                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'La confirmation du mot de passe est incorrecte',
                    'required' => true,
                    'first_options'  => [
                        'label' => 'Mot de passe (entre 8 et 18 caractères, avec chiffres et lettres en minuscules et majuscules) (*)',
                        'attr' => [
                            'placeholder' => 'Création du mot de passe'
                        ]
                    ],
                    'second_options' => [
                        'label' => 'Confirmation du mot de passe (*)',
                        'attr' => [
                            'placeholder' => 'Confirmation du mot de passe'
                        ]
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Vous devez fournir un mot de passe !'
                        ]),
                        new Length([
                            'min' => 8,
                            'max' => 18,
                            'minMessage' => 'Mot de passe trop court ! Minimum {{ limit }} caractères',
                            'maxMessage' => 'Mot de passe trop long ! Maximum {{ limit }} caractères',
                        ])
                    ]
                ]);
            }
            // Dans le cas d'un edit
            else
            {
                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'La confirmation du mot de passe est incorrecte',
                    'required' => false,
                    'first_options'  => [
                        'label' => 'Mot de passe (entre 8 et 18 caractères, avec chiffres et lettres en minuscules et majuscules) (*)',
                        'attr' => [
                            'placeholder' => 'Laisser vide si inchangé'
                        ]
                    ],
                    'second_options' => [
                        'label' => 'Confirmation du mot de passe (*)',
                        'attr' => [
                            'placeholder' => 'Confirmation du mot de passe modifié'
                        ]
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 8,
                            'max' => 18,
                            'minMessage' => 'Mot de passe trop court ! Minimum {{ limit }} caractères',
                            'maxMessage' => 'Mot de passe trop long ! Maximum {{ limit }} caractères',
                        ])
                    ]
                ])
                ->remove('type');
            }
                
        };



        
        $builder
            ->add('username', TextType::class, [
                'label' => 'Identifiant (*)',
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
            // ->add('password')
            ->add('email', EmailType::class, [
                'label' => 'Adresse email (*)',
                'attr' => [
                    'placeholder' => 'Exemple : richard75@gmail.com'
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
                'label' => 'Prénom (*)',
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
                'label' => 'Nom (*)',
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
                'label' => 'Adresse (*)',
                'attr' => [
                    'placeholder' => '117 boulevard des Champs Elysées'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner une adresse'
                    ])
                ]
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code Postal (*)',
                'attr' => [
                    'placeholder' => '75000',
                    'style' => 'width: 200px'
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
                'label' => 'Ville (*)',
                'attr' => [
                    'placeholder' => 'Paris',
                    'style' => 'width: 350px'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner une ville'
                    ])
                ]
            ])
            ->add('longitude', HiddenType::class)
            ->add('latitude', HiddenType::class)
            ->add('avatar', FileType::class, [
                'label' => 'Votre avatar (facultatif)'
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro téléphone fixe (facultatif)',
                'required' => false,
                'attr' => [
                    'placeholder' => '01-20-30-40-50',
                    'style' => 'width: 200px',
                    'class' => 'autoCompletion'
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 16,
                        'minMessage' => 'Numéro de téléphone trop court ! Minimum {{ limit }} caractères',
                        'maxMessage' => 'Numéro de téléphone trop long ! Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('cellNumber', TextType::class, [
                'label' => 'Numéro téléphone mobile (facultatif)',
                'help' => 'Votre numéro sera visible sur le site afin d\'être joignable (uniquement pour nos inscrits).',
                'required' => false,
                'attr' => [
                    'placeholder' => '07-20-30-40-50',
                    'style' => 'width: 200px',
                    'class' => 'autoCompletion'
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 16,
                        'minMessage' => 'Numéro de téléphone trop court ! Minimum {{ limit }} caractères',
                        'maxMessage' => 'Numéro de téléphone trop long ! Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            // ->add('pathCertificat')
            // ->add('isValidated')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('connectedAt')
            ->add('type', ChoiceType::class, [
                'label' => 'Vous êtes... (*)',
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
            ->addEventListener(FormEvents::PRE_SET_DATA, $listener)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
