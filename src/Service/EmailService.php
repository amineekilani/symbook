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

    public function sendConfirmationEmail(string $to, string $username, string $verificationUrl)
    {
        $email = (new Email())
            ->from('aminekilani901@gmail.com')
            ->to($to)
            ->subject('Confirmation de votre inscription - SymBook')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h1 style='color: #4f46e5;'>Bienvenue chez SymBook!</h1>
                    </div>
                    
                    <p>Bonjour <strong>$username</strong>,</p>
                    
                    <p>Merci pour votre inscription sur notre plateforme. Pour pouvoir profiter pleinement de nos services, veuillez confirmer votre adresse e-mail en cliquant sur le bouton ci-dessous :</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verificationUrl' 
                           style='background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Confirmer mon adresse email
                        </a>
                    </div>
                    
                    <div style='background-color: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h2 style='color: #4f46e5; font-size: 18px;'>Ce qui vous attend</h2>
                        <ul style='color: #4b5563; line-height: 1.5;'>
                            <li>Accès à des centaines de livres dans notre catalogue</li>
                            <li>Suivi de vos commandes</li>
                        </ul>
                    </div>
                    
                    <p>Si vous n'avez pas créé de compte sur SymBook, veuillez ignorer ce message.</p>
                    
                    <p>Pour toute question, n'hésitez pas à nous contacter.</p>
                    
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;'>
                        <p>L'équipe SymBook vous souhaite une excellente expérience de lecture!</p>
                    </div>
                </div>
            ");

        $this->mailer->send($email);
    }

    public function sendPaymentConfirmationEmail(string $to, string $username, array $orderDetails, float $total)
    {
        // Construire la liste des articles
        $itemsList = '';
        foreach ($orderDetails as $item) {
            $itemsList .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['title']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['quantity']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['price']} TND</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . ($item['price'] * $item['quantity']) . " TND</td>
                </tr>
            ";
        }

        $email = (new Email())
            ->from('aminekilani901@gmail.com')
            ->to($to)
            ->subject('Confirmation de votre commande - SymBook')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h1 style='color: #4f46e5;'>Merci pour votre commande!</h1>
                    </div>
                    
                    <p>Bonjour <strong>$username</strong>,</p>
                    
                    <p>Nous vous confirmons que votre commande est en cours de traitement et que votre paiement a été traité avec succès.</p>
                    
                    <div style='background-color: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h2 style='color: #4f46e5; font-size: 18px;'>Récapitulatif de la commande</h2>
                        
                        <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                            <thead>
                                <tr style='background-color: #f3f4f6;'>
                                    <th style='padding: 8px; text-align: left; border-bottom: 2px solid #ddd;'>Article</th>
                                    <th style='padding: 8px; text-align: left; border-bottom: 2px solid #ddd;'>Quantité</th>
                                    <th style='padding: 8px; text-align: left; border-bottom: 2px solid #ddd;'>Prix unitaire</th>
                                    <th style='padding: 8px; text-align: left; border-bottom: 2px solid #ddd;'>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                $itemsList
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='3' style='padding: 8px; text-align: right; font-weight: bold;'>Total:</td>
                                    <td style='padding: 8px; font-weight: bold;'>$total TND</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <p>Votre commande sera préparée et expédiée dans les meilleurs délais.</p>
                    
                    <p>Pour toute question concernant votre commande, n'hésitez pas à nous contacter.</p>
                    
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;'>
                        <p>L'équipe SymBook vous remercie pour votre confiance!</p>
                    </div>
                </div>
            ");

        $this->mailer->send($email);
    }

    public function sendPasswordResetEmail(string $to, string $username, string $resetUrl)
    {
        $email = (new Email())
            ->from('aminekilani901@gmail.com')
            ->to($to)
            ->subject('Réinitialisation de votre mot de passe - SymBook')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h1 style='color: #4f46e5;'>Réinitialisation de mot de passe</h1>
                    </div>
                    
                    <p>Bonjour <strong>$username</strong>,</p>
                    
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur SymBook. Veuillez cliquer sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$resetUrl' 
                           style='background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Réinitialiser mon mot de passe
                        </a>
                    </div>
                    
                    <div style='background-color: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='color: #4b5563;'>Ce lien est valide pendant 1 heure. Après cela, vous devrez faire une nouvelle demande de réinitialisation.</p>
                    </div>
                    
                    <p>Si vous n'avez pas demandé la réinitialisation de votre mot de passe, veuillez ignorer ce message.</p>
                    
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;'>
                        <p>L'équipe SymBook vous remercie pour votre confiance!</p>
                    </div>
                </div>
            ");

        $this->mailer->send($email);
    }
}
