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

#[Route('/')]
class HomeController extends AbstractController
{
    // Route to view the book catalogue
    #[Route('/', name: 'home_catalogue', methods: ['GET'])]
    public function catalogue(Request $request, LivreRepository $livreRepository, CategorieRepository $categorieRepository): Response
    {
        // Get search parameters from query string
        $title = $request->query->get('title');
        $editeur = $request->query->get('editeur');  // Change 'author' to 'editeur' here
        $categoryId = $request->query->get('category');

        // Cast categoryId to an integer or null if empty
        $categoryId = $categoryId ? (int)$categoryId : null;

        // Get categories for the filter dropdown
        $categories = $categorieRepository->findAll();

        // Query books by title, editeur (publisher), and category using the custom method
        $livres = $livreRepository->findByFilters($title, $editeur, $categoryId);

        return $this->render('home/catalogue.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'title' => $title,
            'editeur' => $editeur,  // Pass 'editeur' instead of 'author'
            'categoryId' => $categoryId,
            'cart' => $request->getSession()->get('cart', [])
        ]);
    }

    // Route to show a specific book's details
    #[Route('/book/{id}', name: 'home_book_show', methods: ['GET'])]
    public function showBook(Livre $livre, Request $request): Response
    {
        return $this->render('home/book_show.html.twig', [
            'livre' => $livre,
            'cart' => $request->getSession()->get('cart', [])
        ]);
    }

    // Route to add a book to the cart
    #[Route('/add-to-cart/{id}', name: 'home_add_to_cart', methods: ['GET'])]
    public function addToCart(Livre $livre, Request $request): Response
    {
        // Retrieve the session via the request
        $session = $request->getSession();

        // Retrieve the current cart from the session (or an empty array if it doesn't exist)
        $cart = $session->get('cart', []);

        // Check if the book is already in the cart
        $found = false;
        foreach ($cart as &$item) {
            // Compare the book's ID (stored in the cart) with the ID of the current book
            if ($item['id'] === $livre->getId()) {
                // Increase the quantity by 1 if the book is already in the cart
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        // If the book is not already in the cart, add it
        if (!$found) {
            // Store only necessary properties of the book (not the entire Livre object)
            $cart[] = [
                'id' => $livre->getId(),           // Store the book ID
                'title' => $livre->getTitre(),     // Store the book title
                'price' => $livre->getPrix(),      // Store the book price
                'quantity' => 1                    // Initialize the quantity to 1
            ];
        }

        // Save the updated cart back into the session
        $session->set('cart', $cart);

        // Add a flash message for the addition
        $this->addFlash('success', 'Le livre a été ajouté au panier.');

        // Redirect back to the catalogue page
        return $this->redirectToRoute('home_cart');
    }

    // Route to view the current cart
    #[Route("/cart", name: 'home_cart', methods: ['GET'])]
    public function viewCart(Request $request): Response
    {
        // Retrieve the cart from the session
        $cart = $request->getSession()->get('cart', []);

        // Render the cart page
        return $this->render('home/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    // Route to update the quantities in the cart
    #[Route('/update-cart/{id}/{quantity}', name: 'home_update_cart', methods: ['GET'])]
    public function updateCart($id, $quantity, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

        // Check if the book's ID is in the cart
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                // If the quantity is positive, update it
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                } else {
                    // If the quantity is 0, remove the item
                    $cart = array_filter($cart, function($cartItem) use ($id) {
                        return $cartItem['id'] != $id;
                    });
                    $cart = array_values($cart);  // Re-index the array
                }
                break;
            }
        }

        // Save the updated cart back into the session
        $request->getSession()->set('cart', $cart);

        // Redirect to the cart page
        return $this->redirectToRoute('home_cart');
    }
}
