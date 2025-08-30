<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Invitation;
use App\Entity\Pitch;
use App\Enum\InvitationStatus;
use App\Form\TeamType;
use App\Form\JoinTeamType;
use App\Form\PitchType;
use App\Repository\ParticipantRepository;
use App\Repository\CompetitionRepository;
use App\Repository\TeamRepository;
use App\Repository\InvitationRepository;
use App\Repository\PitchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/team')]
class TeamController extends AbstractController
{
    private $security;
    private $entityManager;
    private $participantRepo;
    private $competitionRepo;
    private $teamRepo;
    private $invitationRepo;
    private $pitchRepo;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepo,
        CompetitionRepository $competitionRepo,
        TeamRepository $teamRepo,
        InvitationRepository $invitationRepo,
        PitchRepository $pitchRepo
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->participantRepo = $participantRepo;
        $this->competitionRepo = $competitionRepo;
        $this->teamRepo = $teamRepo;
        $this->invitationRepo = $invitationRepo;
        $this->pitchRepo = $pitchRepo;
    }

    #[Route('/', name: 'team_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }
        $participant = $this->participantRepo->findByUser($user);
        if (!$participant) {
            $this->addFlash('error', 'Vous devez être participé individuellement pour faire partie d\'une équipe');
            return $this->redirectToRoute('inscription');
        }

        return $this->render('team/index.html.twig');
    }

    //il faut maintenir la fonctionalite d inviter participant ; elle ne fonctionne pas pour le moment
    #[Route('/create', name: 'team_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // 1. Get authenticated user and participant
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }
        $participant = $this->participantRepo->findByUser($user);
        if (!$participant) {
            $this->addFlash('error', 'Vous devez être participé individuellement pour faire partie d\'une équipe');
            return $this->redirectToRoute('inscription');
        }

        // 2. Check if already in a team
        if ($participant->getTeam()) {
            $this->addFlash('warning', 'Vous faites déjà partie d\'une équipe.');
            return $this->redirectToRoute('team_index');
        }

        // 3. Get competition
        $competition = $this->competitionRepo->findOneBy(['id' => 1]);
        if (!$competition) {
            $this->addFlash('error', 'La compétition est introuvable.');
            return $this->redirectToRoute('app_dashboard');
        }

        // 4. Create form
        $team = new Team();
        $team->setCompetition($competition)
            ->setTeamCode($this->teamRepo->generateUniqueTeamCode());

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        // 5. Form submission handling
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /*
                // Process member codes
                $memberCodesRaw = $form->get('memberCodes')->getData() ?? '';
                $codesArray = array_unique(preg_split('/[\s,]+/', trim($memberCodesRaw), -1, PREG_SPLIT_NO_EMPTY));

                // Validate team size
                $maxAllowed = $competition->getMaxTeamSize() - 1;
                if (count($codesArray) > $maxAllowed) {
                    $this->addFlash('error', "Maximum {$maxAllowed} membres autorisés");
                    return $this->render('team/create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }*/

                // Set team properties
                $team->setLeaderParticipant($participant)
                    ->setCreatedAt(new \DateTimeImmutable());

                /*
                // Process invitations
                foreach ($codesArray as $code) {
                    if (!preg_match('/^#mia-[A-F0-9]{8}$/i', $code)) {
                        $this->addFlash('error', "Code invalide: {$code}");
                        return $this->render('team/create.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $member = $this->participantRepo->findByCode($code);
                    if (!$member || $member->getTeam()) {
                        $this->addFlash('error', "Membre invalide ou déjà dans une équipe: {$code}");
                        return $this->render('team/create.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    if ($member->getParticipantCode() === $participant->getParticipantCode()) {
                        $this->addFlash('warning', "Vous ne pouvez pas vous ajouter vous-même");
                        return $this->render('team/create.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $invitation = new Invitation();
                    $invitation->setSenderParticipant($participant)
                        ->setReceiverParticipant($member)
                        ->setTeam($team)
                        ->setStatus(InvitationStatus::PENDING)
                        ->setCreatedAt(new \DateTimeImmutable());
                    $this->entityManager->persist($invitation);
                }*/

                // Update participant
                $participant->setTeam($team)
                    ->setIsTeamLeader(true)
                    ->setJoinedTeamDate(new \DateTimeImmutable());

                // Persist changes
                $this->entityManager->persist($team);
                $this->entityManager->persist($participant);
                $this->entityManager->flush();

                return $this->redirectToRoute('team_success', [
                    'teamCode' => $team->getTeamCode(),
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        // 6. Render form (GET or invalid POST)
        return $this->render('team/create.html.twig', [
            'form' => $form->createView(), // Use the SAME form instance
            'competition' => $competition,
        ]);
    }

    #[Route('/join', name: 'team_join', methods: ['GET', 'POST'])]
    public function join(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }
        $participant = $this->participantRepo->findByUser($user);
        if (!$participant) {
            $this->addFlash('error', 'Vous devez être participé individuellement pour faire partie d\'une équipe');
            return $this->redirectToRoute('inscription');
        }

        if ($participant->getTeam()) {
            $this->addFlash('warning', 'Vous faites déjà partie d\'une équipe.');
            return $this->redirectToRoute('team_index');
        }

        $form = $this->createForm(JoinTeamType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teamCode = $form->get('teamCode')->getData();
            $team = $this->teamRepo->findByTeamCode($teamCode);

            if (!$team) {
                $this->addFlash('error', 'Aucune équipe trouvée avec ce code');
                return $this->redirectToRoute('team_join');
            }

            $existingInvitation = $this->invitationRepo->findOneByParticipantsAndTeam(
                $team->getLeaderParticipant(),
                $participant,
                $team
            );

            if ($existingInvitation) {
                $this->addFlash('warning', 'Vous avez déjà une invitation en attente pour cette équipe.');
                return $this->redirectToRoute('team_join');
            }

            $invitation = new Invitation();
            $invitation->setSenderParticipant($participant);
            $invitation->setReceiverParticipant($team->getLeaderParticipant());
            $invitation->setTeam($team);
            $invitation->setStatus(InvitationStatus::PENDING);
            $invitation->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($invitation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre demande a été envouyée avec succès !');
            return $this->redirectToRoute('team_invitations');
        }

        return $this->render('team/join.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pitch', name: 'team_pitch', methods: ['GET', 'POST'])]
    public function pitch(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }
        $participant = $this->participantRepo->findByUser($user);
        if (!$participant) {
            $this->addFlash('error', 'Vous devez être participé individuellement pour faire partie d\'une équipe');
            return $this->redirectToRoute('inscription');
        }

        $existingPitch = $this->pitchRepo->findOneByParticipant($participant);
        if ($existingPitch) {
            $this->addFlash('warning', 'Vous avez déjà publié un pitch.');
            return $this->redirectToRoute('app_pitches');
        }

        $competition = $this->competitionRepo->findOneBy(['id' => 1]);
        if (!$competition) {
            $this->addFlash('error', 'La compétition est introuvable.');
            return $this->redirectToRoute('app_dashboard');
        }

        $pitch = new Pitch();
        $pitch->setCompetition($competition);
        $form = $this->createForm(PitchType::class, $pitch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pitch->setParticipant($participant);
            $this->entityManager->persist($pitch);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre pitch a été publié avec succès !');
            return $this->redirectToRoute('app_pitches');
        }

        return $this->render('team/pitch.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/invitations', name: 'team_invitations', methods: ['GET'])]
    public function invitations(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }

        $participant = $this->participantRepo->findByUser($user);

        $invitations = $this->invitationRepo->findPendingForParticipant($participant);

        return $this->render('team/invitations.html.twig', [
            'invitations' => $invitations,
            'invitationCount' => count($invitations),
            'participant' => $participant,
        ]);
    }

    #[Route('/invitation/{id}/accept', name: 'team_invitation_accept', methods: ['POST'])]
    public function acceptInvitation(Invitation $invitation): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);

        if ($invitation->getReceiverParticipant() !== $participant) {
            throw $this->createAccessDeniedException('Cette invitation ne vous est pas destinée.');
        }

        if ($participant->getTeam() && $participant->isTeamLeader() === false) {
            $this->addFlash('error', 'Vous faites déjà partie d\'une équipe.');
            return $this->redirectToRoute('team_invitations');
        }

        $team = $invitation->getTeam();
        $currentMembers = $this->participantRepo->countTeamMembers($team);
        $competition = $team->getCompetition();

        if ($currentMembers >= $competition->getMaxTeamSize()) {
            $this->addFlash('error', 'L\'équipe a déjà atteint son nombre maximum de membres.');
            return $this->redirectToRoute('team_invitations');
        }

        $newMember = $invitation->getSenderParticipant();

        $newMember->setTeam($team);
        $newMember->setIsTeamLeader(false);
        $newMember->setJoinedTeamDate(new \DateTimeImmutable());
        $invitation->setStatus(InvitationStatus::ACCEPTED);

        $this->entityManager->flush();

        $this->addFlash('success', 'Vous avez accepter la demande de ' . $invitation->getSenderParticipant()->getUser()->getFirstName() . ' ' . $invitation->getSenderParticipant()->getUser()->getLastName() . ' pour rejoigner l\'équipe ' . $team->getName() . ' !');
        return $this->redirectToRoute('team_invitations');
    }

    #[Route('/invitation/{id}/reject', name: 'team_invitation_reject', methods: ['POST'])]
    public function rejectInvitation(Invitation $invitation): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);

        if ($invitation->getReceiverParticipant() !== $participant) {
            throw $this->createAccessDeniedException('Cette invitation ne vous est pas destinée.');
        }

        $invitation->setStatus(InvitationStatus::REJECTED);
        $this->entityManager->flush();

        $this->addFlash('success', 'Invitation déclinée.');
        return $this->redirectToRoute('team_invitations');
    }

    #[Route('/team/creation_success/{teamCode}', name: 'team_success', methods: ['GET'])]
    public function success(string $teamCode): Response
    {
        $team = $this->teamRepo->findOneBy(['teamCode' => $teamCode]);
        if (!$team) {
            $this->addFlash('error', 'Équipe introuvable.');
            return $this->redirectToRoute('team_create');
        }

        return $this->render('team/creation_success.html.twig', [
            'teamCode' => $teamCode,
            'team' => $team,
        ]);
    }

    #[Route('/pitches', name: 'app_pitches', methods: ['GET'])]
    public function pitchs(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }

        $participant = $this->participantRepo->findByUser($user);

        $pitches = $this->pitchRepo->findAll();

        return $this->render('participant/pitches.html.twig', [
            'pitches' => $pitches,
            'pitchesCount' => count($pitches),
            'participant' => $participant,
        ]);
    }
    #[Route('/pitches/delete/{id}', name: 'pitch_delete', methods: ['POST'])]
    public function delete(
        Pitch $pitch,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        // ✅ CSRF protection (security good practice)
        if (!$this->isCsrfTokenValid('delete_pitch_' . $pitch->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Demande invalide');
            return $this->redirectToRoute('app_pitches');
        }

        $em->remove($pitch);
        $em->flush();

        $this->addFlash('success', 'Votre pitch a bien été supprimé');

        return $this->redirectToRoute('app_pitches');
    }

    #[Route('/pitches/modify/{id}', name: 'pitch_modify', methods: ['POST'])]
    public function modifyPitch(Request $request, int $id, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();

        // Authentication check
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Only handle POST requests since this is AJAX-only
        if (!$request->isMethod('POST')) {
            return $this->json(['success' => false, 'error' => 'Méthode non autorisée'], 405);
        }

        // Find the pitch to modify
        $pitch = $this->pitchRepo->find($id);

        // Check if pitch exists
        if (!$pitch) {
            return $this->json(['success' => false, 'error' => 'Pitch n\'existe pas'], 404);
        }

        // Check if user owns this pitch
        $participant = $this->participantRepo->findByUser($user);
        if (!$participant || $pitch->getParticipant()->getId() !== $participant->getId()) {
            return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
        }

        // Validate CSRF token
        $token = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('modify_pitch_' . $id, $token))) {
            return $this->json(['success' => false, 'error' => 'Token CSRF invalide'], 400);
        }

        $content = trim($request->request->get('content'));
        $contactInfo = trim($request->request->get('contactInfo'));

        // Validate input
        $errors = [];

        if (empty($content)) {
            $errors['content'] = 'Le contenu du pitch est requis.';
        } elseif (strlen($content) < 10) {
            $errors['content'] = 'Le contenu doit faire au moins 10 caractères.';
        }

        if (empty($contactInfo)) {
            $errors['contactInfo'] = 'Les informations de contact sont requises.';
        }

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        try {
            // Update the pitch with existing properties only
            $pitch->setContent($content);
            $pitch->setContactInfo($contactInfo); // This expects a non-empty string based on your entity

            $this->entityManager->persist($pitch);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Votre pitch a été modifié avec succès !',
                'pitch' => [
                    'content' => $pitch->getContent(),
                    'contactInfo' => $pitch->getContactInfo()
                ]
            ]);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log('Pitch modification error: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => 'Une erreur s\'est produite lors de la modification'
            ], 500);
        }
    }
}
