<?php

namespace App\Controller\Api;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api', name: 'api_')]
class BookApiController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/search-books', name: 'search_books', methods: ['GET'])]
    public function searchBooks(Request $request, LivreRepository $livreRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->json(['books' => []]);
        }

        // Recherche des livres par titre seulement
        $books = $livreRepository->createQueryBuilder('l')
            ->where('l.titre LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Retourne uniquement les titres
        $booksArray = array_map(function ($book) {
            return $book->getTitre();
        }, $books);

        return $this->json(['books' => $booksArray]);
    }

    #[Route('/ai-assistant', name: 'ai_assistant', methods: ['POST'])]
    public function aiAssistant(Request $request, LivreRepository $livreRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            return $this->json(['reply' => 'Veuillez poser une question ou décrire le type de livre que vous cherchez.']);
        }

        // Récupérer uniquement les titres des livres
        $allBooks = $livreRepository->findAll();
        $bookTitles = array_map(function ($book) {
            return $book->getTitre();
        }, $allBooks);

        // Créer une description simple du catalogue
        $catalogueDescription = "Livres disponibles: " . implode(', ', $bookTitles);

        try {
            // Demander à l'AI de trouver des livres correspondants
            $response = $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($_ENV['AI_API_KEY'] ?? 'your-api-key-here'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek/deepseek-r1',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Tu es l'assistant de la librairie SymBook. Réponds brièvement et propose uniquement les titres des livres qui correspondent à la demande.Dans votre réponse, n'incluez aucun titre ni formatage : commencez directement par du texte brut." .
                            "Catalogue: " . $catalogueDescription
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'temperature' => 0.5
                ],
            ]);

            $responseData = $response->toArray();
            $aiReply = $responseData['choices'][0]['message']['content'] ?? 'Je ne peux pas répondre pour le moment.';

            // Recherche simple dans la base de données basée sur la réponse de l'AI
            $foundBooks = [];
            foreach ($bookTitles as $title) {
                if (stripos($aiReply, $title) !== false) {
                    $foundBooks[] = $title;
                }
            }

            return $this->json([
                'reply' => $aiReply,
                'books' => $foundBooks
            ]);
        } catch (\Exception $e) {
            return $this->json(['reply' => 'Désolé, service indisponible.', 'books' => []], 500);
        }
    }
}