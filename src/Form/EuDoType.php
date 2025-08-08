<?php

namespace App\Form;

use App\Entity\Participant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class EuDoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveauEtude',ChoiceType::class, [
                'label' => 'Niveau d\'étude',
                'choices' => [
                    'Première année' => 'premiere_annee',
                    'Bac+2' => 'bac+2',
                    'Bac+3' => 'bac+3',
                    'Bac+5' => 'bac+5',
                    'Doctorat' => 'doctorat',],
                    'placeholder' => 'Sélectionnez votre niveau d\'étude',
                    'constraints' => [
                        new NotBlank()],
                       ] )
            ->add('Etablissement')
            ->add('CarteAttestation',FileType::class, [
                'label'=> 'Carte d\'étudiant ou attestation de scolarité',
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
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité ',
                'choices' => [
                    'Informatique / Intelligence artificielle / code / robotique' => 'IA',
                    'Électricité / Mécanique / Mécatronique' => 'EMM',
                    'Communication & Marketing' => 'Com-Marketing',
                    'Sciences sociales' => 'Sciences sociales',
                    'Design & Infographie' => 'Design',
                    'IoT & systèmes intelligents' => 'IoT',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Choisissez une spécialité',
                'constraints' => [new NotBlank()],
            ])
            ->add('partage' , ChoiceType::class, [
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