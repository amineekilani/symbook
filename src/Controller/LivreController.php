<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/admin/livre')]
final class LivreController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route(name: 'app_livre_index', methods: ['GET'])]
    public function index(Request $request, LivreRepository $livreRepository, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 5;

        $query = $livreRepository->createQueryBuilder('l')
            ->orderBy('l.id', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $this->render('admin/livre/index.html.twig', [
            'livres' => $pagination,
        ]);
    }

    /**
     * Génère un résumé via API IA en utilisant le titre et l'éditeur
     */
    private function generateSummary(string $titre, string $editeur): string
    {
        try
        {
            $response=$this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . ($_ENV['AI_API_KEY'] ?? 'your-api-key-here'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek/deepseek-r1',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Generate a short summary for a book titled '$titre' published by '$editeur'. In your response, do not include any titles or formatting—just start directly with plain text. The response must be in French"
                    ]
                ]
            ],
        ]);
        $data=$response->toArray();
        if (isset($data['choices'][0]['message']['content']))
        {
            return $data['choices'][0]['message']['content'];
        }
        return 'Impossible de générer un résumé automatiquement.';
        }
        catch (\Exception $e)
        {
            return 'Impossible de générer un résumé automatiquement: ' . $e->getMessage();
        }
    }

    #[Route('/new', name: 'app_livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $slug = $slugger->slug(strtolower($livre->getTitre()))->toString();
            $livre->setSlug($slug);
            $resume=$this->generateSummary($livre->getTitre(), $livre->getEditeur());
            $livre->setResume($resume);
            /** @var UploadedFile $logoFile */
            $logoFile = $form->get('logoFile')->getData();

            if ($logoFile) {
                $newFilename = uniqid().'.'.$logoFile->guessExtension();

                // Déplacez le fichier dans le répertoire public/uploads
                $logoFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $newFilename
                );

                $livre->setImage($newFilename);
            }
            $entityManager->persist($livre);
            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_show', methods: ['GET'])]
    public function show(Livre $livre): Response
    {
        return $this->render('admin/livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $currentLogo = $livre->getImage();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            /** @var UploadedFile $logoFile */
            $logoFile = $form->get('logoFile')->getData();

            if ($logoFile) {
                // Remove old logo if it exists
                if ($currentLogo) {
                    $oldLogoPath = $this->getParameter('kernel.project_dir').'/public/uploads/'.$currentLogo;
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }

                // Upload new logo
                $newFilename = uniqid().'.'.$logoFile->guessExtension();
                $logoFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $newFilename
                );
                $livre->setImage($newFilename);
            }
            $slug = $slugger->slug(strtolower($livre->getTitre()))->toString();
            $livre->setSlug($slug);
            $resume=$this->generateSummary($livre->getTitre(), $livre->getEditeur());
            $livre->setResume($resume);

            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
    }
}
