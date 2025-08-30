<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Competition;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class PhasesController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); 
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isVerified()){
            $this->addFlash('warning','Vous devez vérifier votre compte pour accéder à cette page.');
            return $this->redirectToRoute('app_dashboard');
        }
        $competition = $em->getRepository(Competition::class)->find(1);
        return $this->render('participant/inscriptionPhases.html.twig', [
            'user'=>$user,
            'competition'=>$competition,
        ]);
    }
}