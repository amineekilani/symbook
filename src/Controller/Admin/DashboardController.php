<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Livre;
use DateTime;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le nombre réel d'utilisateurs de la base de données
        $activeUsersCount = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérer le nombre total de livres en stock
        $booksInStock = $entityManager->getRepository(Livre::class)
            ->createQueryBuilder('l')
            ->select('SUM(l.quantite)')
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérer le nombre de commandes d'aujourd'hui
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $todayOrders = $entityManager->getRepository(Commande::class)
            ->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.createdAt >= :today')
            ->andWhere('c.createdAt < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        // Calculer le revenu total
        $totalRevenue = $entityManager->getRepository(Commande::class)
            ->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->getQuery()
            ->getSingleScalarResult();

        // Données pour le dashboard
        $stats = [
            'books_in_stock' => $booksInStock ?: 0,
            'today_orders' => $todayOrders ?: 0,
            'active_users' => $activeUsersCount ?: 0,
            'total_revenue' => $totalRevenue ?: 0,
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

        // Préparer les données pour tous les types de périodes
        $ordersByDate = [];

        // 7 derniers jours
        $orders7Days = $this->getOrdersByPeriod($entityManager, 7, 'day');

        // 30 derniers jours
        $orders30Days = $this->getOrdersByPeriod($entityManager, 30, 'day');

        // 12 derniers mois
        $orders12Months = $this->getOrdersByPeriod($entityManager, 12, 'month');

        // Récupérer le livre avec le score maximum
        $topBook = $entityManager->getRepository(Livre::class)
            ->createQueryBuilder('l')
            ->orderBy('l.score', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recentOrders' => $formattedRecentOrders,
            'loyalCustomers' => $formattedLoyalCustomers,
            'ordersByDate7Days' => $orders7Days,
            'ordersByDate30Days' => $orders30Days,
            'ordersByDate12Months' => $orders12Months,
            'topBook' => $topBook
        ]);
    }

    /**
     * Récupère les commandes pour une période donnée
     */
    private function getOrdersByPeriod(EntityManagerInterface $entityManager, int $amount, string $period): array
    {
        $result = [];
        $today = new DateTime();
        $format = ($period === 'month') ? 'M Y' : 'd M';

        for ($i = $amount - 1; $i >= 0; $i--) {
            $date = clone $today;
            if ($period === 'month') {
                $date->modify("-$i months");
                $startOfPeriod = clone $date;
                $startOfPeriod->modify('first day of this month');
                $startOfPeriod->setTime(0, 0, 0);

                $endOfPeriod = clone $date;
                $endOfPeriod->modify('last day of this month');
                $endOfPeriod->setTime(23, 59, 59);
            } else {
                $date->modify("-$i days");
                $startOfPeriod = clone $date;
                $startOfPeriod->setTime(0, 0, 0);

                $endOfPeriod = clone $date;
                $endOfPeriod->setTime(23, 59, 59);
            }

            $count = $entityManager->getRepository(Commande::class)
                ->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $startOfPeriod)
                ->setParameter('end', $endOfPeriod)
                ->getQuery()
                ->getSingleScalarResult();

            $result[] = [
                'date' => $date->format($format),
                'count' => (int)$count
            ];
        }

        return $result;
    }
}
