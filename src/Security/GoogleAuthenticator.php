<?php

namespace App\Security;

use App\Entity\User as AppUser; // Your main application User entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface; // Keep for type hints
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
// If AppUser doesn't implement UserProviderInterface and you need to look it up by email from UserBadge:
// use Symfony\Component\Security\Core\User\UserProviderInterface as SymfonyUserProviderInterface;


class GoogleAuthenticator extends AbstractAuthenticator
{
    private ClientRegistry $clientRegistry;
    private UrlGeneratorInterface $router;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ClientRegistry $clientRegistry,
        UrlGeneratorInterface $router,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request): ?bool
    {
        // Trigger this authenticator on the route that Google redirects to
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');

        try {
            $accessToken = $client->getAccessToken();
            $googleUser = $client->fetchUserFromToken($accessToken);
        } catch (IdentityProviderException $e) {
            $this->logger->error('[GoogleAuthenticator] OAuth error: '.$e->getMessage());
            throw new AuthenticationException('Failed to get user information from Google', 0, $e);
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $firstName = $googleUser->getFirstName();
        $lastName = $googleUser->getLastName();

        if (empty($email)) {
            throw new AuthenticationException('No email provided by Google');
        }

        $userRepository = $this->entityManager->getRepository(AppUser::class);
        $appUser = $userRepository->findOneBy(['googleId' => $googleId]);

        if ($appUser) {
            // User found by Google ID, optionally update info
            if ($appUser->getEmail() !== $email) {
                $appUser->setEmail($email);
                // Optionally update name fields here
                $this->entityManager->persist($appUser);
                $this->entityManager->flush();
            }
        } else {
            // Try to find user by email if Google ID not found
            $appUser = $userRepository->findOneBy(['email' => $email]);
            if ($appUser) {
                // Link Google ID to existing user
                $appUser->setGoogleId($googleId);
            } else {
                // Create new user
                $appUser = new AppUser();
                $appUser->setEmail($email);
                $appUser->setGoogleId($googleId);
                $appUser->setRoles(['ROLE_USER', 'ROLE_GOOGLE_AUTHENTICATED']);
                // Set a random password to satisfy non-nullable field constraints
                $appUser->setIsVerified(true);
                $appUser->setNom('Ghada azizi');
                $appUser->setPassword(bin2hex(random_bytes(32)));
                // Optionally set first and last names:
                // $appUser->setFirstName($firstName);
                // $appUser->setLastName($lastName);
            }

            $this->entityManager->persist($appUser);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(
            new UserBadge($appUser->getUserIdentifier(), function(string $userIdentifier) use ($userRepository) {
                $user = $userRepository->findOneBy(['email' => $userIdentifier]);
                if (!$user) {
                    throw new AuthenticationException('User not found after creation.');
                }
                return $user;
            })
        );
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info(sprintf(
            '[GoogleAuthenticator] Authentication successful for user "%s" in firewall "%s". Redirecting.',
            $token->getUserIdentifier(),
            $firewallName
        ));
        // Redirect to your desired route after successful login
        return new RedirectResponse($this->router->generate('home_catalogue'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = sprintf(
            '[GoogleAuthenticator] Authentication failed. Reason: %s',
            $exception->getMessage()
        );
        if ($exception->getPrevious()) {
            $message .= ' | Previous: ' . $exception->getPrevious()->getMessage();
        }
        $this->logger->error($message, ['exception' => $exception]);

        $request->getSession()->getFlashBag()->add('error', 'Google authentication failed: ' . $exception->getMessage());
        return new RedirectResponse($this->router->generate('app_login')); // Redirect to your login page
    }
}