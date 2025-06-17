<?php

namespace App\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
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

        $builder->add('password', PasswordType::class, [
            'label' => 'Mot de passe',
            'attr' => [
                'placeholder' => 'Mot de passe',
            ],
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial'
                ])
            ]
        ]);

        $builder->add('confirmPassword', PasswordType::class,
            [
                'label' => 'Confirmer le mot de passe',
                'attr' => [
                    'placeholder' => 'Confirmer le mot de passe',
                ],
                'required' => true
            ]);

        $builder->add('defaultProfilePicture', ChoiceType::class, [
            'label' => 'Choisissez une photo de profil par défaut',
            'mapped' => false,
            'required' => false,
            'choices' => [
                'Avatar 1' => 'profilePicture1.png',
                'Avatar 2' => 'profilePicture2.png',
                'Avatar 3' => 'profilePicture3.png',
                'Avatar 4' => 'profilePicture4.png',
                'Avatar 5' => 'profilePicture5.png',
                'Avatar 6' => 'profilePicture6.png',
                'Avatar 7' => 'profilePicture7.png',
                'Avatar 8' => 'profilePicture8.png',
                'Avatar 9' => 'profilePicture9.png',
            ],
            'expanded' => true,
            'multiple' => false,
            'placeholder' => false,
            'choice_attr' => function($choice, $key, $value) {
                return ['data-img' => '' . $value];
            }
        ]);

        $builder->add('profilePicture', FileType::class, [
            'label' => 'Photo de profil',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).'
                ])
            ],
        ]);

        $builder->add('submit', SubmitType::class,
            [
                'label' => 'S\'inscrire',
                'attr' => [
                    'class' => 'btn btn-success-modal'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}