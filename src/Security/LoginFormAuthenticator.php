<?php

namespace App\Security;

use App\Controller\SecurityController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use App\Security\LoginSuccessHandler;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private SecurityController $securityController;
    private LoginSuccessHandler $loginSuccessHandler;

    public function __construct(
        SecurityController $securityController,
        LoginSuccessHandler $loginSuccessHandler
    ) {
        $this->securityController = $securityController;
        $this->loginSuccessHandler = $loginSuccessHandler;
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->attributes->get('_route') === 'app_login';
    }

    public function authenticate(Request $request): Passport
    {
        // First verify CAPTCHA
        $captchaValid = $this->securityController->verifyCaptcha($request);
        
        if (!$captchaValid) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            if (!$recaptchaResponse) {
                throw new CustomUserMessageAuthenticationException('Le captcha est requis');
            } else {
                throw new CustomUserMessageAuthenticationException('Échec de la vérification du captcha. Veuillez réessayer');
            }
        }

        $username = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Validate inputs
        if (empty($username)) {
            throw new CustomUserMessageAuthenticationException('L\'adresse e-mail est requise');
        }

        if (empty($password)) {
            throw new CustomUserMessageAuthenticationException('Le mot de passe est requis');
        }

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->loginSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->securityController->loginFailureResponse($request, $exception);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->securityController->login($request);
    }
}