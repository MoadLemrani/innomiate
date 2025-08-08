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
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);
        $competitions = $this->competitionRepo->findActiveCompetitions();

        return $this->render('team/index.html.twig', [
            'participant' => $participant,
            'competitions' => $competitions,
        ]);
    }

    #[Route('/create', name: 'team_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);

        if ($participant->getTeam()) {
            $this->addFlash('warning', 'Vous faites déjà partie d\'une équipe.');
            return $this->redirectToRoute('team_index');
        }

        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team->setLeaderParticipant($participant);
            $team->setCreatedAt(new \DateTimeImmutable());

            $participant->setTeam($team);
            $participant->setIsTeamLeader(true);
            $participant->setJoinedTeamDate(new \DateTimeImmutable());

            $memberCodes = $form->get('memberCodes')->getData();
            $codesArray = preg_split('/#mia-\d+/', $memberCodes);

            foreach ($codesArray as $code) {
                if (empty(trim($code))) continue;

                $receiver = $this->participantRepo->findByCode(trim($code));

                if ($receiver) {
                    $invitation = new Invitation();
                    $invitation->setSenderParticipant($participant);
                    $invitation->setReceiverParticipant($receiver);
                    $invitation->setTeam($team);
                    $invitation->setStatus(InvitationStatus::PENDING);
                    $invitation->setCreatedAt(new \DateTimeImmutable());

                    $this->entityManager->persist($invitation);
                }
            }

            $this->entityManager->persist($team);
            $this->entityManager->persist($participant);
            $this->entityManager->flush();

            $this->addFlash('success', 'Équipe créée avec succès ! Voici votre identifiant d\'équipe : ' . $team->getId());
            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/join', name: 'team_join', methods: ['GET', 'POST'])]
    public function join(Request $request): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);

        if ($participant->getTeam()) {
            $this->addFlash('warning', 'Vous faites déjà partie d\'une équipe.');
            return $this->redirectToRoute('team_index');
        }

        $form = $this->createForm(JoinTeamType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teamId = $form->get('teamId')->getData();
            $team = $this->teamRepo->find($teamId);

            if (!$team) {
                $this->addFlash('error', 'Aucune équipe trouvée avec cet identifiant.');
                return $this->redirectToRoute('team_join');
            }

            $existingInvitation = $this->invitationRepo->findOneByParticipantsAndTeam(
                $team->getLeaderParticipant(),
                $participant,
                $team
            );

            if ($existingInvitation) {
                $this->addFlash('warning', 'Vous avez déjà une invitation en attente pour cette équipe.');
                return $this->redirectToRoute('team_index');
            }

            $invitation = new Invitation();
            $invitation->setSenderParticipant($team->getLeaderParticipant());
            $invitation->setReceiverParticipant($participant);
            $invitation->setTeam($team);
            $invitation->setStatus(InvitationStatus::PENDING);
            $invitation->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($invitation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre demande a été envoyée au leader de l\'équipe.');
            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/join.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pitch', name: 'team_pitch', methods: ['GET', 'POST'])]
    public function pitch(Request $request): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);

        $existingPitch = $this->pitchRepo->findOneByParticipant($participant);
        if ($existingPitch) {
            $this->addFlash('warning', 'Vous avez déjà publié un pitch.');
            return $this->redirectToRoute('team_index');
        }

        $pitch = new Pitch();
        $form = $this->createForm(PitchType::class, $pitch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pitch->setParticipant($participant);
            $this->entityManager->persist($pitch);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre pitch a été publié avec succès !');
            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/pitch.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/invitations', name: 'team_invitations', methods: ['GET'])]
    public function invitations(): Response
    {
        $user = $this->security->getUser();
        $participant = $this->participantRepo->findByUser($user);
        $invitations = $this->invitationRepo->findPendingForParticipant($participant);

        return $this->render('team/invitations.html.twig', [
            'invitations' => $invitations,
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

        if ($participant->getTeam()) {
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

        $participant->setTeam($team);
        $participant->setIsTeamLeader(false);
        $participant->setJoinedTeamDate(new \DateTimeImmutable());
        $invitation->setStatus(InvitationStatus::ACCEPTED);

        $this->entityManager->flush();

        $this->addFlash('success', 'Vous avez rejoint l\'équipe ' . $team->getName() . ' !');
        return $this->redirectToRoute('team_index');
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
}
