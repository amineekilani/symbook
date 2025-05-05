<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail(string $to, string $username)
    {
        $email = (new Email())
            ->from('aminekilani901@gmail.com')
            ->to($to)
            ->subject('Confirmation d\'inscription')
            ->html("
                <p>Bonjour <strong>$username</strong>,</p>
                <p>Merci pour votre inscription. Veuillez cliquer sur le lien ci-dessous pour confirmer votre adresse e-mail :</p>
                <p><a href='https://tondomaine.tn/confirmation/$username'>Confirmer mon adresse</a></p>
            ");

        $this->mailer->send($email);
    }
}
