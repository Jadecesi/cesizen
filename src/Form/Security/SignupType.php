<?php

namespace App\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('prenom', TextType::class,
            [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom',
                ],
                'required' => true
            ]);

        $builder->add('nom', TextType::class,
            [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom',
                ],
                'required' => true
            ]);

        $builder->add('username', TextType::class,
            [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'placeholder' => 'Nom d\'utilisateur',
                ],
                'required' => false
            ]);

        $builder->add('email', EmailType::class,
            [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                ],
                'required' => true
            ]);

        $builder->add('dateNaissance', DateType::class,
            [
                'label' => 'Date de naissance',
                'attr' => [
                    'placeholder' => 'Date de naissance',
                ],
                'required' => true
            ]);

        $builder->add('password', PasswordType::class,
            [
                'label' => 'Mot de passe',
                'attr' => [
                    'placeholder' => 'Mot de passe',
                ],
                'required' => true
            ]);

        $builder->add('confirmPassword', PasswordType::class,
            [
                'label' => 'Confirmer le mot de passe',
                'attr' => [
                    'placeholder' => 'Confirmer le mot de passe',
                ],
                'required' => true
            ]);

        $builder->add('submit', SubmitType::class,
            [
                'label' => 'S\'inscrire',
                'attr' => [
                    'class' => 'btn btn-submit-modal'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}