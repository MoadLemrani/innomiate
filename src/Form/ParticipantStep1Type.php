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

class ParticipantStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs personnels
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('courrierProfessionnel')
            ->add('pays')
            ->add('ville')
            ->add('CIN')
            ->add('profession', ChoiceType::class, [
                'choices' => [
                    "Étudiant(e) / Doctorant(e)" => 'poce',
                    "Salarié(e) / Fonctionnaire" => 'safo',
                    "Professeur(e) / chef(fe) d'entreprise" => 'eudo',
                ],
                'placeholder' => 'Choisissez votre profession',
            ]);

        // EventListener pour ajouter le formulaire spécifique en sous-formulaire
        $formModifier = function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $profession = null;

            if (is_array($data) && isset($data['profession'])) {
                $profession = $data['profession'];
            } elseif (is_object($data) && method_exists($data, 'getProfession')) {
                $profession = $data->getProfession();
            }

            // Supprimer ancien sous-formulaire s'il existe
            if ($form->has('specificForm')) {
                $form->remove('specificForm');
            }

            switch ($profession) {
                case 'poce':
                    $form->add('specificForm', PoCeType::class, [
                        'mapped' => true, // selon si tu as les propriétés dans Participant
                        'label' => false,
                    ]);
                    break;
                case 'safo':
                    $form->add('specificForm', SaFoType::class, [
                        'mapped' => true,
                        'label' => false,
                    ]);
                    break;
                case 'eudo':
                    $form->add('specificForm', EuDoType::class, [
                        'mapped' => true,
                        'label' => false,
                    ]);
                    break;
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $formModifier);

        // Important: le POST_SUBMIT du champ profession pour modifier dynamiquement le formulaire
        $builder->get('profession')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $formModifier($event);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
