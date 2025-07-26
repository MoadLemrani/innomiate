<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
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

        return $this->render('participant/dashboard.html.twig');
    }

    #[Route('/supadmin_dashboard', name: 'app_super_admin_dashboard')]
    public function adminDashboard(EntityManagerInterface $entityManager): Response
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

        // Get all users except the current admin
        $users = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $currentUser->getId())
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('super_admin/dashboard.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }
}
