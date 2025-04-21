<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategorieType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class CategoriesController extends AbstractController{
    #[Route('admin/categories', name: 'app_categories')]
    public function index(CategoriesRepository $rep): Response
    {
        $categories=$rep->findAll();
        return $this->render('categories/index.html.twig', [
            'categories' => $categories
        ]);
    }
    //create
    #[Route('/admin/categories/create', name: 'app_categories_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $categorie=new Categories();
        $form=$this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Category created successfully!');
            
            return $this->redirectToRoute("app_categories");
        }
        return $this->render('categories/create.html.twig', [
            'form' => $form
        ]);
    }
}