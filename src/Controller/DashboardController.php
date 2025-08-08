<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompetitionRepository;
use App\Repository\InvitationRepository;
use App\Enum\InvitationStatus;

class DashboardController extends AbstractController
{
    private UserRepository $userRepository;
    private CompetitionRepository $competitionRepository;
    private InvitationRepository $invitationRepository;

    public function __construct(UserRepository $userRepository, CompetitionRepository $competitionRepository, InvitationRepository $invitationRepository)
    {
        $this->userRepository = $userRepository;
        $this->competitionRepository = $competitionRepository;
        $this->invitationRepository = $invitationRepository;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function participantDashboard(): Response
    {
        // Check if user is logged in
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // Only users with ROLE_PARTICIPANT can access this page
        $this->denyAccessUnlessGranted('ROLE_PARTICIPANT');

        // ✅ Fetch active competitions
        $activeCompetitions = $this->competitionRepository->findActiveCompetitions();

        /** @var \App\Entity\User $user */
        $user = $this->getUser(); //creating user interface

        $participants = $user->getParticipants(); // returns Collection
        $count = 0;
        foreach ($participants as $participant) {
            $count += $this->invitationRepository->count([
                'receiverParticipant' => $participant,
                'status' => InvitationStatus::PENDING,
            ]);
        }

        return $this->render('participant/dashboard.html.twig', [
            'activeCompetitions' => $activeCompetitions,
            'invitationCount' => $count,
        ]);
    }

    #[Route('/supadmin_dashboard', name: 'app_super_admin_dashboard')]
    public function adminDashboard(): Response
    {
        // Check if user is logged in
        if (!$this->getUser()) {
            $this->addFlash('error', 'Tu dois être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // Check if user has admin role - if not, show friendly message
        if (!$this->isGranted('ROLE_SUPERADMIN')) {
            $this->addFlash('error', 'Tu n\'as pas les permissions pour accéder à cette page');
            return $this->redirectToRoute('app_dashboard');
        }

        // Get current user with proper type declaration
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Get all users except the current admin using repository method
        $users = $this->userRepository->findAllExceptUser($currentUser);

        // Get additional dashboard data
        $unverifiedUsers = $this->userRepository->findUnverifiedUsers();
        $userStats = $this->userRepository->getUserStatistics();

        return $this->render('super_admin/dashboard.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser,
            'unverifiedUsers' => $unverifiedUsers,
            'userStats' => $userStats
        ]);
    }
}
