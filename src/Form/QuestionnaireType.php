<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $builder->add('events', EntityType::class, [
                'label' => 'Sélectionnez les événements',
                'class' => Event::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
            ]);
        if ($user) {
            $builder->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'button btn-success-modal modalRedirect',
                ]
            ]);
        } else {
            $builder->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'button btn-success-modal modalImbriquer ',
                    'data-target' => 'modal'
                ]
            ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'user' => false
        ]);
    }
}
