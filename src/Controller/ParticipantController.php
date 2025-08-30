<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Participant;
use App\Entity\Competition;
use App\Form\ParticipantStep1Type;
use App\Form\PoCeType;
use App\Form\SaFoType;
use App\Form\EuDoType;
use App\Form\EmptyFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ParticipantController extends AbstractController
{
    private LoggerInterface $logger;
    private Security $security;
    private ValidatorInterface $validator;
    
    // Profession type constants to avoid typos
    private const PROFESSION_PROFESSOR_CHEF = 'ProfesseurChef';
    private const PROFESSION_EMPLOYEE_CIVIL_SERVANT = 'Salarie_Fonctionnaire';
    private const PROFESSION_STUDENT_PHD = 'Etudiant_Doctorant';

    public function __construct(LoggerInterface $logger, Security $security, ValidatorInterface $validator)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->validator = $validator;
    }

    #[Route('/participant', name: 'app_participant')]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        
        // Require authentication
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isVerified()){
            $this->addFlash('warning','Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Check if user already has a participant record
        if ($user && $user->getParticipants()->count() > 0) {
            $this->addFlash(
                'warning',
                'Vous avez déjà participé individuellement. Veuillez compléter la procédure en rejoignant une équipe.'
            );
            return $this->redirectToRoute('inscription');
        }

        $session = $request->getSession();

        // Initialize session if necessary
        if (!$session->has('participant_data')) {
            $this->clearParticipantSession($session);
        }

        // Get participant from session or create new
        $participant = $this->getOrCreateParticipant($session, $user);

        // Create Step 1 form
        $form = $this->createForm(ParticipantStep1Type::class, $participant);
        $form->handleRequest($request);

        $showStep2 = false;
        $specificForm = null;
        $validationErrors = [];

        // Handle Step 1 submission
        if ($form->isSubmitted()) {
            $this->logger->info('Étape 1 soumise');
            
            // Backend validation for Step 1
            $step1Errors = $this->validateStep1($participant, $form);
            
            if ($form->isValid() && empty($step1Errors)) {
                $this->logger->info('Étape 1 soumise avec succès');
                
                try {
                    // Handle identity document upload
                    $documentFile = $form->get('documentIdentite')->getData();
                    if ($documentFile instanceof UploadedFile) {
                        $fileName = $this->handleFileUpload($documentFile, $slugger, 'cin');
                        if ($fileName) {
                            $participant->setCIN($fileName);
                            $session->set('cin_path', $fileName);
                            $session->set('cin_path_user_id', $user->getId());
                        }
                    }

                    // Save data to session
                    $this->saveParticipantDataToSession($participant, $session, $user);
                    
                    // Move to Step 2
                    $showStep2 = true;
                    $this->logger->info('Passage à l\'étape 2');
                    
                } catch (\Exception $e) {
                    $this->logger->error('Erreur lors du traitement de l\'étape 1: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors du traitement du formulaire: ' . $e->getMessage());
                }
            } else {
                // Form has validation errors
                $this->logger->warning('Étape 1: erreurs de validation détectées');
                
                // Add custom validation errors to flash messages
                foreach ($step1Errors as $error) {
                    $this->addFlash('error', $error);
                }
                
                // Add form errors to flash messages
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                
                $validationErrors = $step1Errors;
            }
        }

        // Create profession-specific Step 2 form
        if ($participant->getProfession()) {
            $specificForm = $this->createSpecificForm($participant);
            
            if ($specificForm) {
                $specificForm->handleRequest($request);
                
                // Handle Step 2 submission
                if ($specificForm->isSubmitted()) {
                    $this->logger->info('Étape 2 soumise');
                    
                    // Backend validation for Step 2
                    $step2Errors = $this->validateStep2($participant, $specificForm);
                    
                    if ($specificForm->isValid() && empty($step2Errors)) {
                        $this->logger->info('Étape 2 soumise avec succès');
                        
                        try {
                            // Handle attestation upload if present
                            if ($specificForm->has('CarteAttestation')) {
                                $attestationFile = $specificForm->get('CarteAttestation')->getData();
                                if ($attestationFile instanceof UploadedFile) {
                                    $fileName = $this->handleFileUpload($attestationFile, $slugger, 'attestation');
                                    if ($fileName) {
                                        $participant->setCarteAttestation($fileName);
                                    }
                                }
                            }

                            // Associate the logged-in user
                            $participant->setUser($user);

                            // Set default competition with ID 1
                            $competition = $em->getRepository(Competition::class)->find(1);
                            if (!$competition) {
                                throw $this->createNotFoundException('Default competition not found.');
                            }
                            $participant->setCompetition($competition);

                            // Generate participant code using repository method
                            $participantCode = $em->getRepository(Participant::class)->generateParticipantCode($participant);
                            $participant->setParticipantCode($participantCode);

                            // Validate the complete participant entity
                            $entityErrors = $this->validator->validate($participant);
                            if (count($entityErrors) > 0) {
                                foreach ($entityErrors as $error) {
                                    $this->addFlash('error', $error->getMessage());
                                }
                                throw new \Exception('Validation des données échouée');
                            }

                            // Persist to database within transaction
                            $em->beginTransaction();
                            try {
                                $em->persist($participant);
                                $em->flush();
                                $em->commit();

                                // Clear session after successful save
                                $this->clearParticipantSession($session);

                                $this->addFlash('success', 'Inscription réalisée avec succès !');
                                $this->logger->info('Participant inscrit avec succès: ' . $participantCode);

                                return $this->redirectToRoute('app_participant_success');

                            } catch (\Exception $e) {
                                $em->rollback();
                                throw $e;
                            }

                        } catch (\Exception $e) {
                            $this->logger->error('Erreur lors de l\'enregistrement: ' . $e->getMessage());
                            $this->addFlash('error', 'Erreur lors de l\'inscription: ' . $e->getMessage());
                        }
                    } else {
                        // Step 2 validation errors
                        $this->logger->warning('Étape 2: erreurs de validation détectées');
                        
                        foreach ($step2Errors as $error) {
                            $this->addFlash('error', $error);
                        }
                        
                        foreach ($specificForm->getErrors(true) as $error) {
                            $this->addFlash('error', $error->getMessage());
                        }
                        
                        $showStep2 = true; // Stay on step 2
                    }
                }
            }
        }

        return $this->render('participant/index.html.twig', [
            'form' => $form->createView(),
            'specificForm' => $specificForm ? $specificForm->createView() : null,
            'participant' => $participant,
            'showStep2' => $showStep2,
            'validationErrors' => $validationErrors,
        ]);
    }

    /**
     * Validate Step 1 fields
     */
    private function validateStep1(Participant $participant, $form): array
    {
        $errors = [];

        // Required field validation
        if (empty(trim($participant->getNom() ?? ''))) {
            $errors[] = 'Le nom est obligatoire.';
        }

        if (empty(trim($participant->getPrenom() ?? ''))) {
            $errors[] = 'Le prénom est obligatoire.';
        }

        if (empty(trim($participant->getCourrierProfessionnel() ?? ''))) {
            $errors[] = 'L\'email professionnel est obligatoire.';
        } elseif (!filter_var($participant->getCourrierProfessionnel(), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email professionnel doit être valide.';
        }

        if (empty(trim($participant->getPays() ?? ''))) {
            $errors[] = 'Le pays est obligatoire.';
        }

        if (empty(trim($participant->getVille() ?? ''))) {
            $errors[] = 'La ville est obligatoire.';
        }

        if (empty($participant->getProfession())) {
            $errors[] = 'La profession est obligatoire.';
        }

        // Document validation
        $documentFile = $form->get('documentIdentite')->getData();
        if (!$documentFile instanceof UploadedFile && empty($participant->getCIN())) {
            $errors[] = 'Le document d\'identité est obligatoire.';
        }

        return $errors;
    }

    /**
     * Validate Step 2 fields
     */
    private function validateStep2(Participant $participant, $form): array
    {
        $errors = [];

        // Check if consent/partage is selected
        if ($form->has('partage')) {
            $partageData = $form->get('partage')->getData();
            if (empty($partageData)) {
                $errors[] = 'Vous devez donner votre consentement pour continuer.';
            }
        }

        // Additional profession-specific validation
        $profession = $participant->getProfession();
        
        // You can add specific validation rules based on profession
        switch ($profession) {
            case self::PROFESSION_PROFESSOR_CHEF:
                // Add specific validation for professors/chefs
                break;
            case self::PROFESSION_EMPLOYEE_CIVIL_SERVANT:
                // Add specific validation for employees/civil servants
                break;
            case self::PROFESSION_STUDENT_PHD:
                // Add specific validation for students/PhD students
                break;
        }

        return $errors;
    }

    /**
     * Retrieve participant from session or create new one
     */
    private function getOrCreateParticipant($session, User $user): Participant
    {
        $participant = new Participant();

        // Pre-fill with user data if logged in
        $participant->setNom($user->getFirstName() ?? '');
        $participant->setPrenom($user->getLastName() ?? '');
        $participant->setCourrierProfessionnel($user->getEmail() ?? '');

        // Load session data if it matches current user
        $participantData = $session->get('participant_data');
        if ($participantData && is_array($participantData)) {
            if (!isset($participantData['user_id']) || $participantData['user_id'] === $user->getId()) {
                $this->loadParticipantDataFromSession($participant, $participantData);
                
                // Load CIN path if present and matches user
                if ($session->has('cin_path') && 
                    $session->get('cin_path_user_id') === $user->getId()) {
                    $participant->setCIN($session->get('cin_path'));
                }
            }
        }

        return $participant;
    }

    /**
     * Create profession-specific form
     */
    private function createSpecificForm(Participant $participant)
    {
        $profession = $participant->getProfession();
        
        return match ($profession) {
            self::PROFESSION_PROFESSOR_CHEF => $this->createForm(PoCeType::class, $participant),
            self::PROFESSION_EMPLOYEE_CIVIL_SERVANT => $this->createForm(SaFoType::class, $participant),
            self::PROFESSION_STUDENT_PHD => $this->createForm(EuDoType::class, $participant),
            default => $this->createForm(EmptyFormType::class, $participant)
        };
    }

    /**
     * Handle file upload with validation
     */
    private function handleFileUpload(UploadedFile $file, SluggerInterface $slugger, string $type): ?string
    {
        try {
            // Validate file size (2MB max)
            $maxSize = 2 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                throw new \Exception('Le fichier est trop volumineux (maximum 2MB)');
            }

            // Validate MIME type
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                throw new \Exception('Format de fichier non autorisé. Utilisez PDF, JPG ou PNG.');
            }

            // Validate file extension
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('Extension de fichier non autorisée.');
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Create directory if it doesn't exist
            $uploadDir = $this->getParameter('documents_directory');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $newFilename);
            
            $this->logger->info("Fichier {$type} uploadé avec succès: " . $newFilename);
            return $newFilename;

        } catch (FileException $e) {
            $this->logger->error("Erreur lors de l'upload du fichier {$type}: " . $e->getMessage());
            throw new \Exception('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
        }
    }

    /**
     * Save participant data to session
     */
    private function saveParticipantDataToSession(Participant $participant, $session, User $user): void
    {
        $data = [
            'nom' => $participant->getNom(),
            'prenom' => $participant->getPrenom(),
            'email' => $participant->getCourrierProfessionnel(),
            'pays' => $participant->getPays(),
            'ville' => $participant->getVille(),
            'profession' => $participant->getProfession(),
            'user_id' => $user->getId(),
        ];

        $session->set('participant_data', $data);
        $this->logger->info('Données participant sauvegardées en session');
    }

    /**
     * Load participant data from session
     */
    private function loadParticipantDataFromSession(Participant $participant, array $data): void
    {
        $participant->setNom($data['nom'] ?? $participant->getNom());
        $participant->setPrenom($data['prenom'] ?? $participant->getPrenom());
        $participant->setCourrierProfessionnel($data['email'] ?? $participant->getCourrierProfessionnel());
        $participant->setPays($data['pays'] ?? $participant->getPays());
        $participant->setVille($data['ville'] ?? $participant->getVille());
        $participant->setProfession($data['profession'] ?? $participant->getProfession());
    }

    /**
     * Clear all participant-related session data
     */
    private function clearParticipantSession($session): void
    {
        $keysToRemove = ['participant_data', 'cin_path', 'cin_path_user_id', 'attestation_path'];
        
        foreach ($keysToRemove as $key) {
            $session->remove($key);
        }
        
        $this->logger->info('Session participant nettoyée');
    }

    #[Route('/participant/success', name: 'app_participant_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        
        // Require authentication
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Find participant for current user
        $participant = $em->getRepository(Participant::class)->findOneBy(['user' => $user]);

        if (!$participant || !$participant->getParticipantCode()) {
            $this->addFlash('error', 'Code participant introuvable. Veuillez vous inscrire à nouveau.');
            return $this->redirectToRoute('app_participant');
        }

        $this->logger->info('Affichage page de succès pour participant: ' . $participant->getParticipantCode());

        return $this->render('participant/success.html.twig', [
            'participantCode' => $participant->getParticipantCode(),
            'participant' => $participant
        ]);
    }
}