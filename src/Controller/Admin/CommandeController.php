<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Enum\TStatutCommande;
use App\Repository\CommandeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_admin_commande')]
    public function index(Request $request, CommandeRepository $commandeRepository, PaginatorInterface $paginator): Response
    {
        $statut = $request->query->get('statut');
        $queryBuilder = $commandeRepository->createQueryBuilder('c');

        // Filtre par statut si spécifié
        if ($statut) {
            $enumValue = constant('App\\Enum\\TStatutCommande::' . $statut);
            $queryBuilder->andWhere('c.statut = :statut')
                ->setParameter('statut', $enumValue);
        }

        // Ajouter d'autres conditions si nécessaire
        $queryBuilder->orderBy('c.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 // Nombre d'éléments par page
        );

        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $pagination
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commande_detail')]
    public function detail(Commande $commande): Response
    {
        return $this->render('admin/commande/details.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/change-status/{status}', name: 'app_admin_commande_change_status')]
    public function changeStatus(
        Commande $commande,
        string $status,
        CommandeRepository $commandeRepository
    ): Response {
        $statutMap = [
            'pending' => TStatutCommande::EN_ATTENTE,
            'processing' => TStatutCommande::CONFIRMED,
            'cancelled' => TStatutCommande::ANNULEE
        ];

        if (!array_key_exists($status, $statutMap)) {
            $this->addFlash('error', 'Le statut demandé n\'est pas valide.');
            return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
        }

        $commande->setStatut($statutMap[$status]);
        $commandeRepository->save($commande, true);

        $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
        return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/confirm', name: 'app_admin_commande_confirm')]
    public function confirm(
        Commande $commande,
        CommandeRepository $commandeRepository
    ): Response {
        $commande->setStatut(TStatutCommande::CONFIRMED);
        $commandeRepository->save($commande, true);

        $this->addFlash('success', 'La commande a été confirmée.');
        return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/cancel', name: 'app_admin_commande_cancel')]
    public function cancel(
        Commande $commande,
        CommandeRepository $commandeRepository
    ): Response {
        $commande->setStatut(TStatutCommande::ANNULEE);
        $commandeRepository->save($commande, true);

        $this->addFlash('success', 'La commande a été annulée.');
        return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
    }
}
