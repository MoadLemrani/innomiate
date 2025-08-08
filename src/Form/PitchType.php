<?php

namespace App\Form;

use App\Entity\Pitch;
use App\Entity\Competition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PitchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Votre pitch',
                'attr' => [
                    'placeholder' => 'Décrivez votre idée de projet (objectifs, technologies, besoins...)',
                    'rows' => 5
                ]
            ])
            ->add('contactInfo', TextType::class, [
                'label' => 'Coordonnées',
                'attr' => ['placeholder' => 'Comment vous contacter ?']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pitch::class,
        ]);
    }
}
