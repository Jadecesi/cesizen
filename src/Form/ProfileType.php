<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('nom', TextType::class, [
            'label' => 'Nom',
            'attr' => ['readonly' => true],
        ]);

        $builder->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'attr' => ['readonly' => true],
        ]);

        $builder->add('email', EmailType::class, [
            'label' => 'Email',
            'attr' => ['readonly' => true],
        ]);

        $builder->add('dateNaissance', DateType::class, [
            'label' => 'Date de naissance',
            'attr' => ['readonly' => true],
        ]);

        $builder->add('username', TextType::class, [
            'label' => 'Nom d\'utilisateur',
            'attr' => ['readonly' => true],
            'required' => false,
        ]);

        $builder->add('photoProfile', FileType::class, [
            'label' => 'Photo de profil',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                ])
            ],
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Enregistrer',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
