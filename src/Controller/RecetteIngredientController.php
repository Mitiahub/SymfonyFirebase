<?php

namespace App\Controller;

use App\Entity\RecetteIngredient;
use App\Form\RecetteIngredientType;
use App\Repository\RecetteIngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recette-ingredient')] // Modifié pour éviter les conflits avec d'autres routes
final class RecetteIngredientController extends AbstractController
{
    #[Route(name: 'app_recette_ingredient_index', methods: ['GET'])]
    public function index(RecetteIngredientRepository $recetteIngredientRepository): Response
    {
        // Utilisation de la méthode personnalisée pour inclure les relations
        $recetteIngredients = $recetteIngredientRepository->findAllWithRelations();

        // Debug : Pour afficher les résultats en cas de problème
        // dump($recetteIngredients); die();

        return $this->render('recette_ingredient/index.html.twig', [
            'recette_ingredients' => $recetteIngredients,
        ]);
    }

    #[Route('/new', name: 'app_recette_ingredient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recetteIngredient = new RecetteIngredient();
        $form = $this->createForm(RecetteIngredientType::class, $recetteIngredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($recetteIngredient);
            $entityManager->flush();

            return $this->redirectToRoute('app_recette_ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette_ingredient/new.html.twig', [
            'recette_ingredient' => $recetteIngredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_ingredient_show', methods: ['GET'])]
    public function show(RecetteIngredient $recetteIngredient): Response
    {
        return $this->render('recette_ingredient/show.html.twig', [
            'recette_ingredient' => $recetteIngredient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recette_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RecetteIngredient $recetteIngredient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RecetteIngredientType::class, $recetteIngredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_recette_ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette_ingredient/edit.html.twig', [
            'recette_ingredient' => $recetteIngredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_ingredient_delete', methods: ['POST'])]
    public function delete(Request $request, RecetteIngredient $recetteIngredient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $recetteIngredient->getId(), $request->get('_token'))) {
            $entityManager->remove($recetteIngredient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recette_ingredient_index', [], Response::HTTP_SEE_OTHER);
    }
}
