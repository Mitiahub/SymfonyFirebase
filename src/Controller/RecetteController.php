<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/recettes')]
final class RecetteController extends AbstractController
{
    // ✅ 🔹 Récupérer toutes les recettes (API REST)
        #[Route('/', name: 'api_recette_index', methods: ['GET'])]
    public function getAllRecettes(RecetteRepository $recetteRepository): JsonResponse
    {
        $recettes = $recetteRepository->findAll();
        
        if (!$recettes) {
            return $this->json(['error' => 'Aucune recette trouvée'], 404);
        }

        $data = [];
        foreach ($recettes as $recette) {
            $data[] = [
                'id' => $recette->getId(),
                'nom' => $recette->getNom(),
              'temps_cuisson' => (int) $recette->getTempsCuisson(), // 🔥 Assure que c'est un nombre entier
                'description' => $recette->getDescription(),
                'imageUrl' => $recette->getImageUrl(),
                'prix' => $recette->getPrix(),
            ];
        }

        return $this->json($data);
    }


    // ✅ 🔹 Récupérer une seule recette par son ID (API REST)
    #[Route('/{id}', name: 'api_recette_show', methods: ['GET'])]
    public function getOneRecette(Recette $recette): JsonResponse
    {
        return $this->json([
            'id' => $recette->getId(),
            'nom' => $recette->getNom(),
            'temps_cuisson' => $recette->getTempsCuisson() . ' min',
            'description' => $recette->getDescription(),
            'imageUrl' => $recette->getImageUrl(),
        ]);
    }

    // ✅ 🔹 Ajouter une nouvelle recette (API REST)
    #[Route('/new', name: 'api_recette_new', methods: ['POST'])]
    public function createRecette(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom'], $data['temps_cuisson'], $data['description'])) {
            return $this->json(['error' => 'Données incomplètes'], 400);
        }

        $recette = new Recette();
        $recette->setNom($data['nom']);
        $recette->setTempsCuisson($data['temps_cuisson']);
        $recette->setDescription($data['description']);
        $recette->setImageUrl($data['imageUrl'] ?? null);

        $entityManager->persist($recette);
        $entityManager->flush();

        return $this->json(['message' => 'Recette créée avec succès', 'id' => $recette->getId()], 201);
    }

    // ✅ 🔹 Modifier une recette (API REST)
    #[Route('/{id}/edit', name: 'api_recette_edit', methods: ['PATCH'])]
    public function editRecette(Request $request, Recette $recette, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $recette->setNom($data['nom']);
        }
        if (isset($data['temps_cuisson'])) {
            $recette->setTempsCuisson($data['temps_cuisson']);
        }
        if (isset($data['description'])) {
            $recette->setDescription($data['description']);
        }
        if (isset($data['imageUrl'])) {
            $recette->setImageUrl($data['imageUrl']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Recette mise à jour avec succès']);
    }

    // ✅ 🔹 Supprimer une recette (API REST)
    #[Route('/{id}', name: 'api_recette_delete', methods: ['DELETE'])]
    public function deleteRecette(Recette $recette, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($recette);
        $entityManager->flush();

        return $this->json(['message' => 'Recette supprimée avec succès']);
    }
}
