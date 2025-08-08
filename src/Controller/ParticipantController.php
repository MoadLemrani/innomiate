<?php
// src/Controller/ParticipantController.php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantStep1Type;
use App\Form\PoCeType;
use App\Form\SaFoType;
use App\Form\EuDoType;
use App\Entity\Competition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();

        // 1. Get participant from session or create new
        $participant = $this->getParticipantFromSession($session);

        // 2. Step 1 form (basic participant info)
        $form = $this->createForm(ParticipantStep1Type::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle upload of identity document (CIN)
            $documentFile = $form->get('documentIdentite')->getData();
            if ($documentFile) {
                $fileName = $this->handleFileUpload($documentFile, $slugger, 'cin');
                $participant->setCIN($fileName);
                $session->set('cin_path', $fileName);
            }

            // Save participant data in session (no DB yet)
            $this->saveParticipantDataToSession($participant, $session);
        }

        // 3. Create profession-specific second step form
        $specificForm = $this->createSpecificForm($participant);

        if ($specificForm) {
            $specificForm->handleRequest($request);

            if ($specificForm->isSubmitted() && $specificForm->isValid()) {
                // Handle upload of attestation document
                $attestationFile = $specificForm->get('CarteAttestation')->getData();
                if ($attestationFile) {
                    $fileName = $this->handleFileUpload($attestationFile, $slugger, 'attestation');
                    $participant->setCarteAttestation($fileName);
                    $session->set('attestation_path', $fileName);
                }

                // IMPORTANT: Set the current logged-in user as participant's user
                $user = $this->getUser();
                if (!$user) {
                    throw $this->createAccessDeniedException('You must be logged in to register.');
                }
                $participant->setUser($user);

                // Set default competition with ID 1 ⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️ hard coded
                $competition = $em->getRepository(Competition::class)->find(1);
                if (!$competition) {
                    throw $this->createNotFoundException('Default competition not found.');
                }
                $participant->setCompetition($competition);

                // Generate participant code
                $participantCode = $em->getRepository(Participant::class)->generateParticipantCode($participant);
                $participant->setParticipantCode($participantCode);

                // Persist participant to DB
                try {
                    $em->persist($participant);
                    $em->flush();

                    // Debug logs
                    error_log("📦 Données à persister:");
                    error_log("Nom: " . $participant->getNom());
                    error_log("Profession: " . $participant->getProfession());
                    error_log("CIN: " . $participant->getCIN());
                    error_log("Attestation: " . $participant->getCarteAttestation());
                    error_log("Code: " . $participant->getParticipantCode());

                    // Clear session after successful save
                    $this->clearParticipantSession($session);

                    // Redirect to success page
                    //return $this->redirectToRoute('app_participant_success');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'inscription: ' . $e->getMessage());
                    error_log("❌ Erreur d'enregistrement: " . $e->getMessage());
                }
            }
        }

        // Render form(s) view with session info for debugging
        return $this->render('participant/index.html.twig', [
            'form' => $form->createView(),
            'specificForm' => $specificForm ? $specificForm->createView() : null,
            'session' => $session,
        ]);
    }

    // Helper: retrieve participant data from session or create new Participant
    private function getParticipantFromSession($session): Participant
    {
        $participantData = $session->get('participant_data');
        $participant = new Participant();

        if ($participantData) {
            $participant->setNom($participantData['nom'] ?? '');
            $participant->setPrenom($participantData['prenom'] ?? '');
            $participant->setCourrierProfessionnel($participantData['email'] ?? '');
            $participant->setPays($participantData['pays'] ?? '');
            $participant->setVille($participantData['ville'] ?? '');
            $participant->setProfession($participantData['profession'] ?? '');

            if ($session->has('cin_path')) {
                $participant->setCIN($session->get('cin_path'));
            }
            if ($session->has('attestation_path')) {
                $participant->setCarteAttestation($session->get('attestation_path'));
            }
        }

        return $participant;
    }

    // Helper: save participant data in session (excluding files)
    private function saveParticipantDataToSession(Participant $participant, $session): void
    {
        $session->set('participant_data', [
            'nom' => $participant->getNom(),
            'prenom' => $participant->getPrenom(),
            'email' => $participant->getCourrierProfessionnel(),
            'pays' => $participant->getPays(),
            'ville' => $participant->getVille(),
            'profession' => $participant->getProfession(),
        ]);

        error_log("✅ Données participant sauvées en session");
    }

    // Helper: create specific form depending on profession
    private function createSpecificForm(Participant $participant)
    {
        switch ($participant->getProfession()) {
            case 'ProfesseurChef':
                return $this->createForm(PoCeType::class, $participant);
            case 'Salarie_Fonctionnaire':
                return $this->createForm(SaFoType::class, $participant);
            case 'Etudiant_Doctorant':
                return $this->createForm(EuDoType::class, $participant);
            default:
                error_log("❌ Profession non reconnue: " . $participant->getProfession());
                return null;
        }
    }

    // Helper: handle uploaded files, save with safe unique name
    private function handleFileUpload(UploadedFile $file, SluggerInterface $slugger, string $type): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->getParameter('documents_directory'), $newFilename);

        error_log("✅ Fichier {$type} uploadé: " . $newFilename);
        return $newFilename;
    }

    // Helper: clear all participant-related data from session after success
    private function clearParticipantSession($session): void
    {
        $session->remove('participant_data');
        $session->remove('cin_path');
        $session->remove('attestation_path');

        error_log("🧹 Session participant nettoyée");
    }
}
