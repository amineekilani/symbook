<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeController extends AbstractController
{
    #[Route('/commandes', name: 'app_commandes')]
    #[IsGranted('ROLE_USER')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commandes = $commandeRepository->findCommandesByUser($user);

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_commande_details')]
    #[IsGranted('ROLE_USER')]
    public function details(int $id, CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commande = $commandeRepository->find($id);

        // Vérifier si la commande existe et appartient à l'utilisateur
        if (!$commande || $commande->getUser() !== $user) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('commande/details.html.twig', [
            'commande' => $commande,
        ]);
    }
}