<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ingredients')]
final class IngredientController extends AbstractController
{
    // ✅ 🔹 Récupérer tous les ingrédients (API REST)
    #[Route('/', name: 'api_ingredient_index', methods: ['GET'])]
    public function getAllIngredients(IngredientRepository $ingredientRepository): JsonResponse
    {
        $ingredients = $ingredientRepository->findAll();

        if (!$ingredients) {
            return $this->json(['error' => 'Aucun ingrédient trouvé'], 404);
        }

        $data = [];
        foreach ($ingredients as $ingredient) {
            $data[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
                'quantite_stock' => $ingredient->getQuantiteStock(),
                'seuil_minimum' => $ingredient->getSeuilMinimum(),
                // Ajout de l'image
                'imageUrl' => $ingredient->getImageUrl() ? '/images/' . $ingredient->getImageUrl() : null,
            ];
        }

        return $this->json($data);
    }

    // ✅ 🔹 Récupérer un seul ingrédient par son ID (API REST)
    #[Route('/{id}', name: 'api_ingredient_show', methods: ['GET'])]
    public function getOneIngredient(Ingredient $ingredient): JsonResponse
    {
        return $this->json([
            'id' => $ingredient->getId(),
            'nom' => $ingredient->getNom(),
            'quantite_stock' => $ingredient->getQuantiteStock(),
            'seuil_minimum' => $ingredient->getSeuilMinimum(),
            'imageUrl' => $ingredient->getImageUrl() ? '/images/' . $ingredient->getImageUrl() : null,
        ]);
    }

    // ✅ 🔹 Ajouter un nouvel ingrédient (API REST)
    #[Route('/new', name: 'api_ingredient_new', methods: ['POST'])]
    public function createIngredient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom'], $data['quantite_stock'], $data['seuil_minimum'])) {
            return $this->json(['error' => 'Données incomplètes'], 400);
        }

        $ingredient = new Ingredient();
        $ingredient->setNom($data['nom']);
        $ingredient->setQuantiteStock($data['quantite_stock']);
        $ingredient->setSeuilMinimum($data['seuil_minimum']);
        
        if (isset($data['imageUrl'])) {
            $ingredient->setImageUrl($data['imageUrl']);
        }

        $entityManager->persist($ingredient);
        $entityManager->flush();

        return $this->json(['message' => 'Ingrédient ajouté avec succès', 'id' => $ingredient->getId()], 201);
    }

    // ✅ 🔹 Modifier un ingrédient (API REST)
    #[Route('/{id}/edit', name: 'api_ingredient_edit', methods: ['PATCH'])]
    public function editIngredient(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $ingredient->setNom($data['nom']);
        }
        if (isset($data['quantite_stock'])) {
            $ingredient->setQuantiteStock($data['quantite_stock']);
        }
        if (isset($data['seuil_minimum'])) {
            $ingredient->setSeuilMinimum($data['seuil_minimum']);
        }
        if (isset($data['imageUrl'])) {
            $ingredient->setImageUrl($data['imageUrl']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Ingrédient mis à jour avec succès']);
    }

    // ✅ 🔹 Supprimer un ingrédient (API REST)
    #[Route('/{id}', name: 'api_ingredient_delete', methods: ['DELETE'])]
    public function deleteIngredient(Ingredient $ingredient, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($ingredient);
        $entityManager->flush();

        return $this->json(['message' => 'Ingrédient supprimé avec succès']);
    }
}
