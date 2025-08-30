<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Get the user who just logged in
        $user = $token->getUser();
        
        // Get their roles
        $roles = $user->getRoles();

        // Check what role they have and send them to the right page
        if (in_array('ROLE_SUPERADMIN', $roles)) {
            // If they are SUPERADMIN, send them to admin page
            $route = 'app_admin';
        } elseif (in_array('ROLE_PARTICIPANT', $roles)) {
            // If they are PARTICIPANT, send them to participant page
            $route = 'app_dashboard';
        } else {
            // If they have no special role, send them to participant page as default
            $route = 'app_dashboard';
        }

        error_log('Final route: ' . $route);

        // Redirect the user to their page
        return new RedirectResponse($this->router->generate($route));
    }
}
