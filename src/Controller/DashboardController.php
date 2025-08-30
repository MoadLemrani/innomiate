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
        foreach ($participants as $participant) {//this invitation count is for the future when there is a lot of competition to regiser in
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
}
