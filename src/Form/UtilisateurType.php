<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => 'readonly'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => 'readonly'
                ]
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'attr' => [
                    'placeholder' => 'Date de naissance',
                    'readonly' => 'readonly'
                ],
                'required' => true
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('apiToken', TextType::class, [
                'label' => 'Token API',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('photoProfile', HiddenType::class, [
                'mapped' => false,
                'attr' => [
                    'readonly' => 'readonly'
                ]
            ])
            ->add('tokenExpiresAt', DateType::class, [
                'label' => 'Token Expire le',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nom'
            ]);

        $builder->add('modifier', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => [
                'class' => 'button edit-btn'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}