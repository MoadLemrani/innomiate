<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\Competition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de l\'équipe est obligatoire',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom de l\'équipe doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom de l\'équipe ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Donnez un nom à votre équipe',
                ],
            ])
            ->add('competition', EntityType::class, [
                'class' => Competition::class,
                'choice_label' => 'name',
                'label' => 'Compétition',
                'placeholder' => 'Sélectionnez une compétition',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une compétition',
                    ]),
                ],
            ])
            ->add('memberCodes', TextareaType::class, [
                'label' => 'Codes des membres',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez les codes participant de vos coéquipiers, séparés par des virgules',
                    'rows' => 4,
                ],
                'help' => 'Vous pouvez ajouter des membres. Chacun recevra une invitation.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}