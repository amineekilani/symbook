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
<<<<<<< HEAD
    #[Route('/', name: 'home_catalogue', methods: ['GET'])]
    public function catalogue(Request $request, LivreRepository $livreRepository, CategorieRepository $categorieRepository): Response
    {
        $title = $request->query->get('title');
        $editeur = $request->query->get('editeur');
        $categoryId = $request->query->get('category');
        $categoryId = $categoryId ? (int)$categoryId : null;

        $categories = $categorieRepository->findAll();
=======
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
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
        $livres = $livreRepository->findByFilters($title, $editeur, $categoryId);

        return $this->render('home/catalogue.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'title' => $title,
<<<<<<< HEAD
            'editeur' => $editeur,
=======
            'editeur' => $editeur,  // Pass 'editeur' instead of 'author'
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
            'categoryId' => $categoryId
        ]);
    }

<<<<<<< HEAD
=======
    // Route to show a specific book's details
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
    #[Route('/book/{id}', name: 'home_book_show', methods: ['GET'])]
    public function showBook(Livre $livre): Response
    {
        return $this->render('home/book_show.html.twig', [
            'livre' => $livre,
        ]);
    }

<<<<<<< HEAD
    #[Route('/add-to-cart/{id}', name: 'home_add_to_cart', methods: ['GET'])]
    public function addToCart(Livre $livre, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['id'] === $livre->getId()) {
=======
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
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

<<<<<<< HEAD
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
=======
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
        return $this->redirectToRoute('home_catalogue');
    }

    // Route to view the current cart
    #[Route("/cart", name: 'home_cart', methods: ['GET'])]
    public function viewCart(Request $request): Response
    {
        // Retrieve the cart from the session
        $cart = $request->getSession()->get('cart', []);

        // Render the cart page
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
        return $this->render('home/cart.html.twig', [
            'cart' => $cart
        ]);
    }

<<<<<<< HEAD
=======
    // Route to update the quantities in the cart
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
    #[Route('/update-cart/{id}/{quantity}', name: 'home_update_cart', methods: ['GET'])]
    public function updateCart($id, $quantity, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

<<<<<<< HEAD
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                } else {
                    $cart = array_filter($cart, function($cartItem) use ($id) {
                        return $cartItem['id'] != $id;
                    });
                    $cart = array_values($cart);
=======
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
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
                }
                break;
            }
        }

<<<<<<< HEAD
        $request->getSession()->set('cart', $cart);
=======
        // Save the updated cart back into the session
        $request->getSession()->set('cart', $cart);

        // Redirect to the cart page
>>>>>>> dd5e241228992cae19e01f3d868f01b3c55e4923
        return $this->redirectToRoute('home_cart');
    }
}
