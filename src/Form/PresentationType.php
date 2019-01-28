<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Species;
use App\Entity\Presentation;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PresentationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        // TRES IMPORTANT
        // Permet d'accéder aux informations du User connecté
        // https://symfony.com/doc/current/security/entity_provider.html#using-a-custom-query-to-load-the-user
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         // Ajout d'un listener
         $listener = function (FormEvent $event) {
            $presentation = $event->getData();
            $form = $event->getForm();
            $user = $this->security->getUser();

            // dump($user);
            
            // Si le user n'est pas petsitter 
            if($user->getType() !== 'petsitter')
            {
                    $form->remove('species')
                        ->remove('services')
                        ->remove('price')
                        ;
            }
        };

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de votre présentation',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'indiquer un titre !'
                    ])
                ]
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Présentez-vous',
                'attr' => [
                    'rows' => 7
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez fournir une description !'
                    ])
                ]
            ])
            // ->add('isActive')
            // ->add('slug')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('user')
            ->add('species', EntityType::class, [
                'label' => 'Catégorie d\'espèces pour le petsitting',
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
            ->add('price', TextType::class, [
                'label' => 'Prix / heure'
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, $listener)
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Presentation::class,
        ]);
    }
}
