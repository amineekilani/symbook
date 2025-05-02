<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setIsVerified(false); // L'utilisateur n'est pas vérifié par défaut

            $entityManager->persist($user);
            $entityManager->flush();

            // Générer le lien de vérification
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // Créer et envoyer l'email
            $email = (new Email())
                ->from('aminekilani901@gmail.com')
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre adresse email')
                ->html(sprintf(
                    '<h1>Bienvenue sur notre site !</h1>
                    <p>Pour confirmer votre adresse email, veuillez cliquer sur le lien suivant :</p>
                    <p><a href="%s">Confirmer mon email</a></p>
                    <p>Ce lien expirera dans 24 heures.</p>',
                    $signatureComponents->getSignedUrl()
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Un email de confirmation a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
