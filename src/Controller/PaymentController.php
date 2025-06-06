<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Enum\TStatutCommande;
use App\Repository\LivreRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'checkout')]
    public function checkout(Request $request): Response
    {
        // Récupérer le panier de la session
        $cart = $request->getSession()->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('home_cart');
        }

        // Calculer le total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Stocker les détails de la commande en session pour les utiliser après le paiement
        $request->getSession()->set('order_details', [
            'items' => $cart,
            'total' => $total
        ]);

        // Initialiser Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Préparer les line items pour Stripe
        $lineItems = [];
        foreach ($cart as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['title'],
                    ],
                    'unit_amount' => $item['price'] * 100, // Montant en centimes
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Créer la session Stripe Checkout
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', [], 0),
            'cancel_url' => $this->generateUrl('payment_cancel', [], 0),
        ]);

        return $this->redirect($checkoutSession->url);
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(
        Request $request,
        EmailService $emailService,
        EntityManagerInterface $entityManager,
        LivreRepository $livreRepository
    ): Response {
        // Récupérer les détails de la commande
        $orderDetails = $request->getSession()->get('order_details', []);
        $cart = $orderDetails['items'] ?? [];
        $total = $orderDetails['total'] ?? 0;

        // Obtenir l'utilisateur connecté
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Enregistrer la commande dans la base de données
        if ($user) {
            // Créer une nouvelle commande
            $commande = new Commande();
            $commande->setUser($user);
            $commande->setTotal($total);
            $commande->setCommandeDetails(json_encode($cart));
            $commande->setStatut(TStatutCommande::EN_ATTENTE);

            $entityManager->persist($commande);

            // Mettre à jour les stocks de livres
            foreach ($cart as $item) {
                $livre = $livreRepository->find($item['id']);
                if ($livre) {
                    // Calculer la nouvelle quantité
                    $newQuantity = $livre->getQuantite() - $item['quantity'];
                    // S'assurer que la quantité ne devient pas négative
                    $livre->setQuantite(max(0, $newQuantity));

                    // Incrémenter le score du livre avec la quantité achetée
                    $livre->setScore($livre->getScore() + $item['quantity']);

                    // Persister les changements
                    $entityManager->persist($livre);
                }
            }

            // Enregistrer tous les changements en base de données
            $entityManager->flush();

            // Envoyer l'email de confirmation
            $emailService->sendPaymentConfirmationEmail(
                $user->getEmail(),
                $user->getNom() ?? 'Client',
                $cart,
                $total
            );
        }

        // Vider le panier et les détails de commande après un paiement réussi
        $request->getSession()->remove('cart');
        $request->getSession()->remove('order_details');

        $this->addFlash('success', 'Paiement réussi ! Votre commande a été traitée et les stocks ont été mis à jour.');
        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->addFlash('info', 'Le paiement a été annulé.');
        return $this->redirectToRoute('home_cart');
    }
}
