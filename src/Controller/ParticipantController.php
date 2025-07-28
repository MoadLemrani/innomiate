<?php
namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantStep1Type;
use App\Form\PoCeType;
use App\Form\SaFoType;
use App\Form\EuDoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantStep1Type::class, $participant);
        $form->handleRequest($request);

        $specificFormView = null;

        // Étape 1 : Soumission du formulaire principal
        if ($form->isSubmitted() && $form->isValid()) {
            $profession = $participant->getProfession();

            // Créer dynamiquement le bon formulaire complémentaire
            switch ($profession) {
                case 'poce':
                    $specificForm = $this->createForm(PoCeType::class, $participant);
                    break;
                case 'safo':
                    $specificForm = $this->createForm(SaFoType::class, $participant);
                    break;
                case 'eudo':
                    $specificForm = $this->createForm(EuDoType::class, $participant);
                    break;
                default:
                    throw new \Exception('Profession non reconnue');
            }

            // Gérer la soumission du formulaire complémentaire
            $specificForm->handleRequest($request);

            if ($specificForm->isSubmitted() && $specificForm->isValid()) {
                
                $em->persist($participant);
                $em->flush();

                return $this->redirectToRoute('app_participant');
            }

            $specificFormView = $specificForm->createView();
        }

        // Affichage
        return $this->render('participant/index.html.twig', [
            'form' => $form->createView(),
            'specificForm' => $specificFormView,
        ]);
    }
}
