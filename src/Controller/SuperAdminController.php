<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\TeamRepository;
use App\Entity\Participant;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Competition;

final class SuperAdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $user, ParticipantRepository $participant, TeamRepository $teamRepository): Response
    {
        // ✅ Auth check
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            $this->addFlash('error', 'Tu n\'as pas les permissions pour accéder à cette page');
            return $this->redirectToRoute('app_dashboard');
        }

        $rows = $user->findAll([], ['createdAt' => 'DESC']);
        $parts = $participant->findAll([], ['createdAt' => 'DESC']);
        $superadmins = array_filter($rows, function ($user) {
            return in_array('ROLE_SUPERADMIN', $user->getRoles());
        });
        $teams = $teamRepository->findAll();

        return $this->render('super_admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'rows' => $rows,
            'parts' => $parts,
            'superadmins' => $superadmins,
            'teams' => $teams,
        ]);
    }

    #[Route('/admin/{id}/toggle-role', name: 'toggle_role')]
    public function toggleRole(User $user, EntityManagerInterface $em): RedirectResponse
    {
        // ✅ Auth check
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les permissions pour accéder à cette page');
            return $this->redirectToRoute('app_dashboard');
        }

        // Si déjà superadmin on enlève et met user
        if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
            $user->setRoles(['ROLE_PARTICIPANT']);
            $this->addFlash('success', 'Rôle changé en utilisateur standard');
        } else {
            $user->setRoles(['ROLE_SUPERADMIN']);
            $this->addFlash('success', 'Rôle changé en superadministrateur');
        }

        $em->flush();
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $em): RedirectResponse
    {
        // ✅ Auth check
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            $this->addFlash('error', 'Tu n\'as pas les permissions pour accéder à cette page');
            return $this->redirectToRoute('app_dashboard');
        }

        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/{id}/modifier', name: 'modifier_participant', methods: ['POST'])]
    public function modifierParticipant(
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        // ✅ Auth check
        if (!$this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Tu dois être connecté pour accéder à cette page']);
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            return new JsonResponse(['success' => false, 'message' => 'Tu `\'as pas les permissions pour accéder à cette page']);
        }

        $participant = $participantRepository->find($id);

        if (!$participant) {
            return new JsonResponse(['success' => false, 'message' => 'Participant introuvable']);
        }

        $competition = $em->getRepository(Competition::class)->find(1);

        $teamCode = $request->request->get('teamCode');
        $teamName = $request->request->get('teamName');

        if (!$teamCode || !$teamName) {
            return new JsonResponse(['success' => false, 'message' => 'Veuillez saisir le code et le nom de l\'équipe']);
        }

        // Vérifier que l'équipe existe avec ce code
        $team = $em->getRepository(\App\Entity\Team::class)
            ->findOneBy(['teamCode' => $teamCode]);

        if (!$team) {
            return new JsonResponse(['success' => false, 'message' => 'Code d\'équipe introuvable']);
        }

        // Vérifier que le nom correspond (optionnel)
        if ($team->getName() !== $teamName) {
            return new JsonResponse(['success' => false, 'message' => 'Le nom de l\'équipe ne correspond pas au code']);
        }

        if (count($team->getMembers()) >= $competition->getMaxTeamSize()) {
            return new JsonResponse([
                'success' => false,
                'message' => "Cette équipe a déjà {$competition->getMaxTeamSize()} participants"
            ]);
        }

        // Affecter le participant à l'équipe
        $participant->setTeam($team);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'teamName' => $team->getName(),
            'teamCode' => $team->getTeamCode()
        ]);
    }

    #[Route('/admin/{id}/delete-participant', name: 'participant_delete')]
    public function deleteParticipant(
        Participant $participant,
        EntityManagerInterface $em,
        TeamRepository $teamRepo
    ): RedirectResponse {
        // ✅ Auth check
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            $this->addFlash('error', 'Tu n\'as pas les permissions pour accéder à cette page');
            return $this->redirectToRoute('app_dashboard');
        }

        // Find teams where this participant is the leader
        $teamsLed = $teamRepo->findBy(['leaderParticipant' => $participant]);

        foreach ($teamsLed as $team) {
            // Try to assign a new leader from the team's members (excluding the participant we delete)
            $otherMembers = $team->getMembers()->filter(function ($m) use ($participant) {
                return $m->getId() !== $participant->getId();
            });

            if (!$otherMembers->isEmpty()) {
                $newLeader = $otherMembers->first();
                if ($newLeader instanceof Participant) {
                    $team->setLeaderParticipant($newLeader);

                    // If you have an isTeamLeader flag on Participant, update it.
                    if (method_exists($newLeader, 'setIsTeamLeader')) {
                        $newLeader->setIsTeamLeader(true);
                    }
                } else {
                    // fallback: clear leader
                    $team->setLeaderParticipant(null);
                }
            } else {
                // no other members => clear leader so FK won't block deletion
                $team->setLeaderParticipant(null);
            }
        }

        // Now safe to remove the participant
        $em->remove($participant);
        $em->flush();

        $this->addFlash('success', 'Participant supprimé avec succès');
        return $this->redirectToRoute('app_admin');
    }
    #[Route('/admin/get-team/{id}', name: 'get_team')]
    public function getTeam(TeamRepository $teamRepo, int $id): JsonResponse
    {
        // ✅ Auth check
        if (!$this->getUser()) {
            return $this->json(['error' => 'Tu dois être connecté pour accéder à cette page'], 401);
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            return $this->json(['error' => 'Tu n\'as pas les permissions pour accéder à cette page'], 403);
        }

        $team = $teamRepo->find($id);

        if (!$team) {
            return $this->json(['error' => 'Équipe introuvable'], 404);
        }

        return $this->json([
            'id' => $team->getId(),
            'name' => $team->getName(),
            'teamCode' => $team->getTeamCode(),
            'createdAt' => $team->getCreatedAt() ? $team->getCreatedAt()->format('Y-m-d') : null,
            'leaderParticipant' => $team->getLeaderParticipant() ? [
                'id' => $team->getLeaderParticipant()->getId(),
                'prenom' => $team->getLeaderParticipant()->getPrenom(),
                'nom' => $team->getLeaderParticipant()->getNom(),
                'participantCode' => $team->getLeaderParticipant()->getParticipantCode(),
                'email' => $team->getLeaderParticipant()->getCourrierProfessionnel(),
            ] : null,
            'members' => array_map(fn($m) => [
                'id' => $m->getId(),
                'prenom' => $m->getPrenom(),
                'nom' => $m->getNom(),
                'participantCode' => $m->getParticipantCode(),
                'email' => $m->getCourrierProfessionnel()
            ], $team->getMembers()->toArray())
        ]);
    }

    #[Route('/admin/team/{id}/add-member', name: 'admin_add_member', methods: ['POST'])]
    public function addMember(
        int $id,
        Request $request,
        TeamRepository $teamRepo,
        ParticipantRepository $participantRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        // ✅ Auth check
        if (!$this->getUser()) {
            return $this->json(['success' => false, 'message' => 'Tu dois être connecté pour accéder à cette page'], 401);
        }

        // ✅ Role check
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            return $this->json(['success' => false, 'message' => 'Tu n\'as pas les permissions pour accéder à cette page'], 403);
        }

        $team = $teamRepo->find($id);
        if (!$team) {
            return $this->json(['success' => false, 'message' => 'Équipe introuvable'], 404);
        }

        $participantCode = trim((string) $request->request->get('participantCode', ''));
        if ($participantCode === '') {
            return $this->json(['success' => false, 'message' => 'Le code participant est requis'], 400);
        }

        $participant = $participantRepo->findOneBy(['participantCode' => $participantCode]);
        if (!$participant) {
            return $this->json(['success' => false, 'message' => 'Participant introuvable'], 404);
        }

        // déjà dans cette équipe ?
        if ($participant->getTeam() && $participant->getTeam()->getId() === $team->getId()) {
            return $this->json(['success' => false, 'message' => 'Ce participant est déjà dans cette équipe'], 400);
        }

        // déjà dans une autre équipe ?
        if ($participant->getTeam() && $participant->getTeam()->getId() !== $team->getId()) {
            return $this->json(['success' => false, 'message' => 'Ce participant appartient déjà à une autre équipe'], 400);
        }
        $competition = $em->getRepository(Competition::class)->find(1);
        // Limite membres
        if ($team->getMembers()->count() >= $competition->getMaxTeamSize()) {
            return $this->json(['success' => false, 'message' => "Cette équipe a déjà {$competition->getMaxTeamSize()} membres"], 400);
        }

        // Association côté Participant (relation OneToMany/ManyToOne la plus courante)
        $participant->setTeam($team);

        if ($team->getMembers()->isEmpty()) {
            $team->setLeaderParticipant($participant);
        }

        $em->flush();

        return $this->json(['success' => true, 'message' => 'Membre ajouté avec succès']);
    }

    /**
     * Suppression d'un membre de l'équipe
     * On autorise DELETE et POST (au cas où DELETE serait bloqué par l'hébergeur)
     */
    #[Route('/admin/team/{teamId}/remove-member/{memberId}', name: 'admin_remove_member', methods: ['DELETE', 'POST'])]
    public function removeMember(
        int $teamId,
        int $memberId,
        TeamRepository $teamRepo,
        ParticipantRepository $participantRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$this->getUser()) {
            return $this->json(['success' => false, 'message' => 'Tu dois être connecté pour accéder à cette page'], 401);
        }

        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            return $this->json(['success' => false, 'message' => 'Tu n\'as pas les permissions pour accéder à cette page'], 403);
        }

        $team = $teamRepo->find($teamId);
        $participant = $participantRepo->find($memberId);

        if (!$team || !$participant) {
            return $this->json(['success' => false, 'message' => 'Équipe ou membre introuvable'], 404);
        }

        if ($participant->getTeam()?->getId() !== $team->getId()) {
            return $this->json(['success' => false, 'message' => 'Ce participant n\'appartient pas à cette équipe'], 400);
        }

        // ✅ Check if participant is the current leader
        $isLeader = $team->getLeaderParticipant() && $team->getLeaderParticipant()->getId() === $participant->getId();

        // ✅ Get remaining members BEFORE removing
        $remainingMembers = $team->getMembers()->filter(fn($m) => $m->getId() !== $participant->getId());

        // Detach participant
        $participant->setTeam(null);

        // If leader, assign new leader if possible
        if ($isLeader) {
            if (!$remainingMembers->isEmpty()) {
                $newLeader = $remainingMembers->first();
                $team->setLeaderParticipant($newLeader);

                if (method_exists($newLeader, 'setIsTeamLeader')) {
                    $newLeader->setIsTeamLeader(true);
                }
            } else {
                $team->setLeaderParticipant(null);
            }
        }

        $em->flush();

        return $this->json(['success' => true, 'message' => 'Membre supprimé avec succès']);
    }
}
