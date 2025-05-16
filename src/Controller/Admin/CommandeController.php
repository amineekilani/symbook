<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commande')]
final class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_admin_commande')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commande_detail')]
    public function detail(Commande $commande): Response
    {
        return $this->render('admin/commande/detail.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/change-status/{status}', name: 'app_admin_commande_change_status')]
    public function changeStatus(
        Commande $commande,
        string $status,
        CommandeRepository $commandeRepository
    ): Response {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $this->addFlash('error', 'Le statut demandé n\'est pas valide.');
            return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
        }

        $commande->setStatus($status);
        $commandeRepository->save($commande, true);

        $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
        return $this->redirectToRoute('app_admin_commande_detail', ['id' => $commande->getId()]);
    }
}
