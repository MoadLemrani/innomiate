<?php
namespace App\Form;

use App\Form\PoCeType;
use App\Form\SaFoType;
use App\Form\EuDoType;
use App\Entity\Participant; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;





class ParticipantStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs personnels
        $builder
            ->add('nom',TextType::class,[
                'label' =>'Entrez votre nom'            ])
            ->add('prenom' , TextType::class, [
                'label' => 'Entrez votre prénom'
            ])
            ->add('courrierProfessionnel', EmailType::class,[
                'label' => 'Courrier professionnel',
                'attr' =>[ 'placeholder' => 'exemple@domaine.com'],
                'constraints' =>[
                    new NotBlank([
                        'message' => 'Veuillez entrer votre adresse email.',
                    ]),
                    new Email([
                        'message' =>  'L’adresse "{{ value }}" n’est pas une adresse email valide.',
                    ]),
                ],
            ])
            ->add('pays')
            ->add('ville')
            ->add('documentIdentite', FileType::class, [
             'label' => "Carte d'identité ou passeport (PDF, JPG ou PNG)",
             'mapped' => false, // car ce n'est pas un champ de l'entité
             'required' => true,
        'constraints' => [
            new File([
                'maxSize' => '2M',
                'mimeTypes' => [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                ],
                'mimeTypesMessage' => 'Merci de télécharger un fichier PDF ou image valide.',
            ])
        ],
    ])
     ->add('profession', ChoiceType::class, [
        'label' =>'Vous êtes ' ,
                'choices' => [
                    "Étudiant(e) / Doctorant(e)" => 'Etudiant_Doctorant',
                    "Salarié(e) / Fonctionnaire" => 'Salarie_Fonctionnaire',
                    "Professeur(e) / chef(fe) d'entreprise" => 'ProfesseurChef',
                ],
                'placeholder' => 'Choisissez votre profession',
            ]);

       

           

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}