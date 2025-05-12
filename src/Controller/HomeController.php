<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/home')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home_catalogue', methods: ['GET'])]
    public function catalogue(Request $request, LivreRepository $livreRepository, CategorieRepository $categorieRepository): Response
    {
        $title = $request->query->get('title');
        $editeur = $request->query->get('editeur');
        $categoryId = $request->query->get('category');
        $categoryId = $categoryId ? (int)$categoryId : null;

        $categories = $categorieRepository->findAll();
        $livres = $livreRepository->findByFilters($title, $editeur, $categoryId);

        return $this->render('home/catalogue.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'title' => $title,
            'editeur' => $editeur,
            'categoryId' => $categoryId
        ]);
    }

    #[Route('/book/{id}', name: 'home_book_show', methods: ['GET'])]
    public function showBook(Livre $livre): Response
    {
        return $this->render('home/book_show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/add-to-cart/{id}', name: 'home_add_to_cart', methods: ['GET'])]
    public function addToCart(Livre $livre, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['id'] === $livre->getId()) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'id' => $livre->getId(),
                'title' => $livre->getTitre(),
                'price' => $livre->getPrix(),
                'quantity' => 1
            ];
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Le livre a été ajouté au panier.');

        return $this->redirectToRoute('home_catalogue');
    }

    #[Route("/cart", name: 'home_cart', methods: ['GET'])]
    public function viewCart(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        return $this->render('home/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    #[Route('/update-cart/{id}/{quantity}', name: 'home_update_cart', methods: ['GET'])]
    public function updateCart($id, $quantity, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                } else {
                    $cart = array_filter($cart, function($cartItem) use ($id) {
                        return $cartItem['id'] != $id;
                    });
                    $cart = array_values($cart);
                }
                break;
            }
        }

        $request->getSession()->set('cart', $cart);
        return $this->redirectToRoute('home_cart');
    }
}
