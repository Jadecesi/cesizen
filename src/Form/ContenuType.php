<?php

namespace App\Form;

use App\Entity\Contenu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('titre', TextType::class, [
            'label' => 'Titre',
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('image', FileType::class, [
            'label' => 'Image',
            'mapped' => false,
            'required' => false,
            'error_bubbling' => true,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG).',
                ])
            ],
            'attr' => [
                'style' => 'border-left: 4px solid var(--accent-color);',
            ],
        ]);

        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('url', TextType::class, [
            'label' => 'Url',
            'attr' => [
                'class' => 'form-control',
            ],
            'required' => false,
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => [
                'class' => 'btn btn-success-modal',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contenu::class,
        ]);
    }
}
