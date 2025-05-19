<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Données statiques pour l'exemple
        $stats = [
            'books_in_stock' => 1248,
            'today_orders' => 42,
            'active_users' => 873,
            'total_revenue' => 12489,
        ];

        // Récupérer les commandes récentes
        $recentOrders = $entityManager->getRepository(Commande::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->select('c.id', 'c.statut', 'c.total', 'u.nom as customerName')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Formater les données des commandes récentes
        $formattedRecentOrders = [];
        foreach ($recentOrders as $order) {
            // Convertir l'enum en string
            $status = $order['statut'];
            if ($status instanceof \App\Enum\TStatutCommande) {
                $status = $status->value;
            }

            $formattedRecentOrders[] = [
                'id' => $order['id'],
                'customer' => $order['customerName'] ?? 'Client inconnu',
                'status' => $status,
                'total' => $order['total'],
            ];
        }

        // Récupérer les clients fidèles
        // Nous utiliserons 'total' au lieu de 'montantTotal' en nous basant sur l'erreur
        $loyalCustomers = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->innerJoin('u.commandes', 'c')
            ->select(
                'u.id',
                'u.email',
                'u.nom',
                'u.roles',
                'COUNT(c.id) as orderCount',
                'SUM(c.total) as totalSpent'
            )
            ->groupBy('u.id')
            ->orderBy('totalSpent', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Formater les données des clients fidèles
        $formattedLoyalCustomers = [];
        foreach ($loyalCustomers as $customer) {
            $formattedLoyalCustomers[] = [
                'id' => $customer['id'],
                'name' => $customer['nom'],
                'orders' => $customer['orderCount'],
                'total_spent' => number_format($customer['totalSpent'], 2),
                'status' => $customer['totalSpent'] > 1000 ? 'VIP' : 'Fidèle',
                'email' => $customer['email'],
            ];
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recentOrders' => $formattedRecentOrders,
            'loyalCustomers' => $formattedLoyalCustomers,
        ]);
    }
}
