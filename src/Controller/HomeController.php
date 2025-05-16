<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Knp\Component\Pager\PaginatorInterface;
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
    public function catalogue(Request $request, CategorieRepository $categorieRepository, LivreRepository $livreRepository, PaginatorInterface $paginator): Response
    {
        $title = $request->query->get('title', '');
        $editeur = $request->query->get('editeur', '');
        $categoryId = $request->query->get('category', '');

        $query = $livreRepository->findByFilters($title, $editeur, $categoryId);

        // Pagination - 4 livres par page
        $livres = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),  // Paramètre page dans l'URL, par défaut 1
            4                                    // Nombre d'éléments par page
        );

        return $this->render('home/catalogue.html.twig', [
            'livres' => $livres,
            'title' => $title,
            'editeur' => $editeur,
            'categoryId' => $categoryId,
            'categories' => $categorieRepository->findAll(),
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
                // Check if adding one more would exceed available quantity
                if ($item['quantity'] + 1 > $livre->getQuantite()) {
                    $this->addFlash('warning', 'Vous avez atteint la quantité maximale disponible pour ce livre.');
                    return $this->redirectToRoute('home_cart');
                }

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
                'image' => $livre->getImage() ?? null,     // Store the book image
                'quantity' => 1,                   // Initialize the quantity to 1
                'maxQuantity' => $livre->getQuantite()    // Store the maximum available quantity
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
    public function viewCart(Request $request, LivreRepository $livreRepository): Response
    {
        // Retrieve the cart from the session
        $cart = $request->getSession()->get('cart', []);

        // Update max quantity for each item in the cart
        foreach ($cart as &$item) {
            $livre = $livreRepository->find($item['id']);
            if ($livre) {
                $item['maxQuantity'] = $livre->getQuantite();
            }
        }

        // Save the updated cart back into the session
        $request->getSession()->set('cart', $cart);

        // Render the cart page
        return $this->render('home/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    // Route to update the quantities in the cart
    #[Route('/update-cart/{id}/{quantity}', name: 'home_update_cart', methods: ['GET'])]
    public function updateCart($id, $quantity, Request $request, LivreRepository $livreRepository): Response
    {
        $cart = $request->getSession()->get('cart', []);

        // Get the book to check its available quantity
        $livre = $livreRepository->find($id);

        if (!$livre) {
            $this->addFlash('error', 'Livre non trouvé.');
            return $this->redirectToRoute('home_cart');
        }

        // Ensure the requested quantity doesn't exceed available stock
        if ($quantity > $livre->getQuantite()) {
            $quantity = $livre->getQuantite();
            $this->addFlash('warning', 'La quantité a été ajustée au stock disponible.');
        }

        // Check if the book's ID is in the cart
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                // If the quantity is positive, update it
                if ($quantity > 0) {
                    $item['quantity'] = $quantity;
                    $item['maxQuantity'] = $livre->getQuantite();
                } else {
                    // If the quantity is 0, remove the item
                    $cart = array_filter($cart, function ($cartItem) use ($id) {
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

    // Route to remove a book from the cart
    #[Route('/remove-from-cart/{id}', name: 'home_remove_from_cart', methods: ['GET'])]
    public function removeFromCart($id, Request $request): Response
    {
        // Retrieve the cart from the session
        $cart = $request->getSession()->get('cart', []);

        // Filter out the item with the specified ID
        $cart = array_filter($cart, function ($item) use ($id) {
            return $item['id'] != $id;
        });

        // Re-index the array
        $cart = array_values($cart);

        // Save the updated cart back into the session
        $request->getSession()->set('cart', $cart);

        // Add a flash message
        $this->addFlash('success', 'L\'article a été supprimé du panier.');

        // Redirect back to the cart page
        return $this->redirectToRoute('home_cart');
    }
}
