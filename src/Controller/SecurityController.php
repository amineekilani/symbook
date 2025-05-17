<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
#[Route(path: '/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
// Si déjà connecté, rediriger ailleurs (optionnel)
// if ($this->getUser()) {
//     return $this->redirectToRoute('home_catalogue');
// }

// Récupérer l'erreur de connexion, s'il y en a
$error = $authenticationUtils->getLastAuthenticationError();
// Dernier email saisi
$lastUsername = $authenticationUtils->getLastUsername();

return $this->render('security/login.html.twig', [
'last_username' => $lastUsername,
'error' => $error,
]);
}

// Lancer la redirection vers Google OAuth
#[Route(path: '/connect/google', name: 'connect_google_start')]
public function connectGoogle(ClientRegistry $clientRegistry)
{
// Redirige vers Google pour s'authentifier
return $clientRegistry
->getClient('google') // le nom configuré dans knpu_oauth2_client.yaml
->redirect(['email', 'profile']); // Scopes demandés à Google
}

// Cette route est maintenant gérée par l'authentificateur OAuth2
#[Route(path: '/connect/google/check', name: 'connect_google_check')]
public function connectGoogleCheck(Request $request)
{
// Ce contrôleur ne doit plus être appelé directement, car l'authentification est gérée par l'authentificateur
throw new \Exception('This should never be reached directly.');
}

#[Route(path: '/logout', name: 'app_logout')]
public function logout(): void
{
// This method can be blank - it will be intercepted by the logout key on your firewall.
throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
}
}
