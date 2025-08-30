<?php
// src/Controller/SupportController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    #[Route('/support', name: 'support')]
    public function index(): Response
    {
        $user = $this->getUser(); 
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('participant/support.html.twig', [
            'user'=>$user,
        ]);
    }
}