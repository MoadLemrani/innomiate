<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ParticipantRepository;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(ParticipantRepository $participantRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        $participant = $participantRepository->findOneBy(['user' => $user]);
        $team = $participant ? $participant->getTeam() : null;
        $membres = $team ? $team->getMembers() : [];
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'participant' => $participant,
            'user' => $user,
            'team' => $team,
            'membres' => $membres,
        ]);
    }
}
