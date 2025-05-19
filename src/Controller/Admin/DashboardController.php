<?php

namespace App\Controller\Admin;

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

        $recentOrders = [
            [
                'id' => 'ORD-1001',
                'customer' => 'Jean Dupont',
                'status' => 'Livré',
                'total' => 45.99,
            ],
            [
                'id' => 'ORD-1002',
                'customer' => 'Marie Martin',
                'status' => 'En cours',
                'total' => 89.50,
            ],
            [
                'id' => 'ORD-1003',
                'customer' => 'Pierre Bernard',
                'status' => 'Expédié',
                'total' => 32.25,
            ],
            [
                'id' => 'ORD-1004',
                'customer' => 'Sophie Leroy',
                'status' => 'Annulé',
                'total' => 67.80,
            ],
            [
                'id' => 'ORD-1005',
                'customer' => 'Luc Petit',
                'status' => 'Livré',
                'total' => 120.00,
            ],
        ];

        // Récupérer les clients fidèles
        // Nous utiliserons 'total' au lieu de 'montantTotal' en nous basant sur l'erreur
        $loyalCustomers = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->innerJoin('u.commandes', 'c')
            ->select('u.id', 'u.email', 'u.nom', 'u.roles', 
                    'COUNT(c.id) as orderCount', 
                    'SUM(c.total) as totalSpent')
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
            'recentOrders' => $recentOrders,
            'loyalCustomers' => $formattedLoyalCustomers,
        ]);
    }
}
