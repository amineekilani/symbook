<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_dashboard')]
    public function index(): Response
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

        $loyalCustomers = [
            [
                'name' => 'Sophie Leroy',
                'orders' => 15,
                'total_spent' => 1245,
                'status' => 'VIP',
            ],
            [
                'name' => 'Jean Dupont',
                'orders' => 12,
                'total_spent' => 980,
                'status' => 'VIP',
            ],
            [
                'name' => 'Marie Martin',
                'orders' => 9,
                'total_spent' => 765,
                'status' => 'Régulier',
            ],
            [
                'name' => 'Pierre Bernard',
                'orders' => 7,
                'total_spent' => 620,
                'status' => 'Régulier',
            ],
            [
                'name' => 'Luc Petit',
                'orders' => 6,
                'total_spent' => 540,
                'status' => 'Régulier',
            ],
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'loyalCustomers' => $loyalCustomers,
        ]);
    }
}
