<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegistrationController extends AbstractController
{
    private HttpClientInterface $httpClient;
    public function __construct(private EmailVerifier $emailVerifier, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->verifyCaptcha($request)) {
                $this->addFlash('error', 'Veuillez compléter le captcha.');
                return $this->redirectToRoute('app_register');
            } else {

                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $entityManager->persist($user);
                $entityManager->flush();

                // send email confirmation
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('dev@innomiate.local', 'Innomiate Registration'))
                        ->to($user->getEmail())
                        ->subject('Vérifiez votre adresse e-mail')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                        ->context([
                            'user' => $user, //pass the user name explicitly
                        ])
                );

                // Automatically log in the user after registration
                $security->login($user);

                // Add flash message about email verification
                $this->addFlash('info', 'Bienvenue ! Veuillez vérifier votre boîte e-mail pour valider votre compte');

                // Redirect to dashboard or home page instead of check email
                return $this->redirectToRoute('app_dashboard');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
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

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        try {
            // Get user from the verification token instead of current session
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse e-mail a été vérifiée. Merci !');

        // Redirect to dashboard
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/verify/resend', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur invalide'], 401);
        }

        if ($user->isVerified()) {
            return new JsonResponse(['success' => false, 'message' => 'Adresse e-mail déjà vérifiée'], 400);
        }

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('resend_verification', $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        }

        try {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('dev@innomiate.local', 'Innomiate Registration'))
                    ->to($user->getEmail())
                    ->subject('Vérifiez votre adresse e-mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'user' => $user,
                    ])
            );

            return new JsonResponse(['success' => true, 'message' => 'E-mail de vérification renvoyé avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'e-mail'], 500);
        }
    }
}
