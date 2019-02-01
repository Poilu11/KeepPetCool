<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du service (*)',
                'attr' => [
                    'placeholder' => 'Exemple : Gardiennage'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner nom de service.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Nom trop court ! Minimum {{ limit }} caractères',
                        'maxMessage' => 'Nom trop long ! Maximum {{ limit }} caractères',
                    ])
                ]
            ])
            //->add('presentations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
