<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/categorie')]
final class CategorieController extends AbstractController{
    #[Route(name: 'app_categorie_index', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $categorieRepository, LivreRepository $livreRepository, PaginatorInterface $paginator): Response
    {
        $categories = $categorieRepository->findAll();
        $livres = $livreRepository->findAll();

        $page = $request->query->getInt('page', 1);
        $limit = 5;

        $query = $categorieRepository->createQueryBuilder('l')
            ->orderBy('l.id', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        // Logique pour trouver la catégorie la plus populaire
        $categoriePopulaire = null;
        if (!empty($categories)) {
            $categoriePopulaire = array_reduce($categories, function ($carry, $item) {
                return ($carry === null || count($item->getLivres()) > count($carry->getLivres())) ? $item : $carry;
            });
        }

        return $this->render('admin/categorie/index.html.twig', [

            'categories' => $pagination,
            'categoriesTotal' => $categories,
            'livres' => $livres,
            'categoriePopulaire' => $categoriePopulaire,
        ]);
    }

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug=$slugger->slug(strtolower($categorie->getLibelle()))->toString();
            $categorie->setSlug($slug);
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('admin/categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
