<?php

namespace App\Form;

use App\Entity\Participant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;


class SaFoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Etablissement')
            ->add('CarteAttestation',FileType::class, [
                'label'=> 'Attestation de travail',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' =>'Merci de télécharger un fichier PDF ou image valide.',
            ])]
            ])
            ->add('statut',ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Responsable'=> 'responsable',
                    'Ingénieur & Développeur'=> 'ingenieur_developpeur',
                    'Chargé de communication & marketing'=> 'charge_communication_marketing',
                    'Designer'=> 'designer',
                    'Ouvrier'=> 'ouvrier',
                    'Autre'=> 'autre',
                ],
                'placeholder' => 'Choisissez un statut',
                'constraints' => [new NotBlank()],
                    
            ])
           ->add('partage', ChoiceType::class, [
            'label'=> 'Par la présente, je certifie l\'exactitude des informations fournies et je consens à les partager avec le comité scientifique de l\'événement à des fins organisationnelles conformément à la loi 09-08.',

            'choices' => [
                'Oui - Envoyer' => true,
                'Non - je souhaite rectifier quelques informations' => false,
            ],
            'expanded' => true,
            'multiple' => false,]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}