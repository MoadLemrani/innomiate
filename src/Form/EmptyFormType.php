<?php
// src/Form/EmptyFormType.php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmptyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirmation', ChoiceType::class, [
                'choices' => [
                    'Oui, je confirme' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'label' => 'Confirmation',
                'data' => false, // Valeur par défaut
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'csrf_protection' => true,
        ]);
    }
}