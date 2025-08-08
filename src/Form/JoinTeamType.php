<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class JoinTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamId', TextType::class, [
                'label' => 'Identifiant d\'équipe',
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'identifiant d\'équipe est obligatoire',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Entrez l\'identifiant fourni par votre leader',
                ],
                'help' => 'Demandez cet identifiant au leader de l\'équipe que vous souhaitez rejoindre',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}