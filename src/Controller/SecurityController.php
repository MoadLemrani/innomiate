<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private AuthenticationUtils $authenticationUtils;

    public function __construct(
        HttpClientInterface $httpClient,
        AuthenticationUtils $authenticationUtils
    ) {
        $this->httpClient = $httpClient;
        $this->authenticationUtils = $authenticationUtils;
    }

    #[Route(path: '/', name: 'app_login')]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $siteKey = $_ENV['RECAPTCHA_SITE_KEY'];

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $siteKey,
        ]);
    }

    public function verifyCaptcha(Request $request): bool
    {
        $recaptchaResponse = $request->request->get('g-recaptcha-response');

        if (!$recaptchaResponse) {
            return false;
        }

        try {
            $verifyResponse = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $_ENV['RECAPTCHA_SECRET'],
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->getClientIp(),
                ],
            ]);

            $result = $verifyResponse->toArray();
            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            // Log the error if needed
            return false;
        }
    }

    public function loginFailureResponse(Request $request, AuthenticationException $exception): Response
    {
        $lastUsername = $request->request->get('_username', '');
        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $exception,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}