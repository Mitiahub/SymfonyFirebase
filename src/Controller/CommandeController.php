<?php

namespace App\Controller;

use App\Entity\Commande;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Entity\CommandeRecette;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Verifier;

#[Route('/api/commande')]
class CommandeController extends AbstractController
{
 

    private function decodeFirebaseToken(string $token): ?string
    {
        $jwt = str_replace('Bearer ', '', $token);

        try {
            $decoded = JWT::decode($jwt, new Key($_ENV['FIREBASE_PUBLIC_KEY'], 'RS256'));
            return $decoded->sub ?? null;
        } catch (\Exception $e) {
            error_log("Erreur de décodage JWT : " . $e->getMessage());
            return null;
        }
    }

    //  Récupérer toutes les commandes
            #[Route('/', name: 'api_commande_index', methods: ['GET'])]
        public function getUserCommandes(Request $request, EntityManagerInterface $entityManager): JsonResponse
        {
            $token = $request->headers->get('Authorization');

            if (!$token) {
                return $this->json(['error' => 'Token manquant'], 401);
            }

            $firebaseUid = $this->decodeFirebaseToken($token); // ✅ Décoder le token Firebase

            if (!$firebaseUid) {
                return $this->json(['error' => 'Token invalide'], 403);
            }

            $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['firebaseUid' => $firebaseUid]);

            if (!$user) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $commandes = $entityManager->getRepository(Commande::class)->findBy(['utilisateur' => $user]);

            if (!$commandes) {
                return $this->json(['error' => 'Aucune commande trouvée pour cet utilisateur'], 404);
            }

            $data = [];
            foreach ($commandes as $commande) {
                $data[] = [
                    'id' => $commande->getId(),
                    'status' => $commande->getStatus(),
                    'montant_total' => $commande->getMontantTotal(),
                    'created_at' => $commande->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user_uid' => $user->getFirebaseUid(),
                ];
            }

            return $this->json($data);
        }

    
        private function verifierFirebaseToken(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        $idToken = substr($authorizationHeader, 7); // 🔥 Supprimer "Bearer "
        
        try {
            $verifier = new Verifier('test-11714'); // Remplace par ton ID projet Firebase
            $verifiedIdToken = $verifier->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->get('sub'); // 🔥 `uid` Firebase
        } catch (InvalidToken $e) {
            return null;
        }
    }

    
    #[Route('/passer', name: 'api_commande_passer', methods: ['POST'])]
    public function passerCommande(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $userUid = $this->verifierFirebaseToken($request); // ✅ Récupérer `uid` depuis `idToken`
    
        if (!$userUid) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // ✅ Trouver l'utilisateur dans la base (si nécessaire)
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['firebaseUid' => $userUid]);
    
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['recettes']) || !is_array($data['recettes'])) {
            return $this->json(['error' => 'Données invalides'], 400);
        }
    
        $commande = new Commande();
        $commande->setStatus('en attente');
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setUpdatedAt(new \DateTimeImmutable());
        $commande->setMontantTotal(0);
        $commande->setUtilisateur($user);
    
        $entityManager->persist($commande);
        $entityManager->flush();
    
        $recetteRepo = $entityManager->getRepository(Recette::class);
        $montantTotal = 0;
    
        foreach ($data['recettes'] as $recetteData) {
            $recette = $recetteRepo->find($recetteData['recette_id']);
            if (!$recette) {
                return $this->json(['error' => "Recette ID {$recetteData['recette_id']} non trouvée"], 404);
            }
    
            $commandeRecette = new CommandeRecette();
            $commandeRecette->setCommande($commande);
            $commandeRecette->setRecette($recette);
            $commandeRecette->setQuantite($recetteData['quantite']);
    
            $entityManager->persist($commandeRecette);
    
            $montantTotal += $recetteData['quantite'] * 10; // 🔹 Prix fixe par recette
        }
    
        $commande->setMontantTotal($montantTotal);
        $entityManager->flush();
    
        return $this->json([
            'message' => 'Commande passée avec succès',
            'commande_id' => $commande->getId(),
            'montant_total' => $montantTotal,
            'user_uid' => $userUid,
        ]);
    }
    
        // 📌 Créer une nouvelle commande
        #[Route('/new', name: 'api_commande_new', methods: ['POST'])]
        public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
        {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['user_id']) || !isset($data['montant_total'])) {
                return $this->json(['error' => 'Données invalides'], 400);
            }

            $user = $entityManager->getRepository(Utilisateur::class)->find($data['user_id']);
            if (!$user) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $commande = new Commande();
            $commande->setStatus('en attente');
            $commande->setCreatedAt(new \DateTimeImmutable());
            $commande->setUpdatedAt(new \DateTimeImmutable());
            $commande->setMontantTotal($data['montant_total']);
            $commande->setUtilisateur($user);

            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->json([
                'message' => 'Commande créée avec succès',
                'commande_id' => $commande->getId()
            ]);
        }
            #[Route('/utilisateur/{firebaseUid}', name: 'api_commande_par_utilisateur', methods: ['GET'])]
    public function getUserCommandesByUid(string $firebaseUid, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['firebaseUid' => $firebaseUid]);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $commandes = $entityManager->getRepository(Commande::class)->findBy(['utilisateur' => $user]);

        if (!$commandes) {
            return $this->json(['error' => 'Aucune commande trouvée pour cet utilisateur'], 404);
        }

        $data = [];
        foreach ($commandes as $commande) {
            $data[] = [
                'id' => $commande->getId(),
                'status' => $commande->getStatus(),
                'montant_total' => $commande->getMontantTotal(),
                'created_at' => $commande->getCreatedAt()->format('Y-m-d H:i:s'),
                'user_uid' => $user->getFirebaseUid(),
            ];
        }

        return $this->json($data);
    }

    private function envoyerNotification($deviceToken, $title, $body)
    {
        $factory = (new Factory)->withServiceAccount(__DIR__.'/../firebase_credentials.json');
        $messaging = $factory->createMessaging();

        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        try {
            $messaging->send($message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    // 📌 Récupérer une commande spécifique
    #[Route('/{id}', name: 'api_commande_show', methods: ['GET'])]
    public function show(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        return $this->json([
            'id' => $commande->getId(),
            'status' => $commande->getStatus(),
            'montant_total' => $commande->getMontantTotal(),
            'created_at' => $commande->getCreatedAt()->format('Y-m-d H:i:s'),
            'user_id' => $commande->getUtilisateur()->getId(),
        ]);
    }

    // 📌 Mettre à jour une commande
    #[Route('/{id}/edit', name: 'api_commande_edit', methods: ['PATCH'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['status'])) {
            $commande->setStatus($data['status']);
        }

        if (isset($data['montant_total'])) {
            $commande->setMontantTotal($data['montant_total']);
        }

        $commande->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json([
            'message' => 'Commande mise à jour',
            'commande_id' => $commande->getId()
        ]);
    }

    // 📌 Terminer une commande
        #[Route('/{id}/complete', name: 'api_commande_complete', methods: ['PATCH'])]
    public function complete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        // Vérification du stock avant validation
        if (!$this->verifierStockDisponible($commande)) {
            return $this->json(['error' => 'Stock insuffisant pour finaliser la commande'], 400);
        }

        // Mise à jour des stocks
        $this->mettreAJourStock($commande);

        // Mise à jour du statut
        $commande->setStatus('terminée');
        $commande->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Commande terminée avec succès, stock mis à jour',
            'commande_id' => $commande->getId()
        ]);
    }

    // 📌 Supprimer une commande
    #[Route('/{id}', name: 'api_commande_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->json(['message' => 'Commande supprimée']);
    }

        #[Route('/{id}/add-recette', name: 'api_commande_add_recette', methods: ['POST'])]
    public function addRecetteToCommande(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['recettes']) || !is_array($data['recettes'])) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $recetteRepo = $entityManager->getRepository(Recette::class);

        foreach ($data['recettes'] as $recetteData) {
            $recette = $recetteRepo->find($recetteData['recette_id']);

            if (!$recette) {
                return $this->json(['error' => "Recette ID {$recetteData['recette_id']} non trouvée"], 404);
            }

            // Ajout de la recette à la commande
            $commande->addRecette($recette);

            // Ajout automatique des ingrédients nécessaires à la commande
            foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                $ingredient = $recetteIngredient->getIngredient();
                $commande->addIngredient($ingredient);
            }

            $commandeRecette = new CommandeRecette();
            $commandeRecette->setCommande($commande);
            $commandeRecette->setRecette($recette);
            $commandeRecette->setQuantite($recetteData['quantite']);

            $entityManager->persist($commandeRecette);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Recettes et leurs ingrédients ajoutés à la commande']);
    }


    #[Route('/{id}/recettes', name: 'api_commande_recettes', methods: ['GET'])]
    public function getCommandesRecettes(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $recettes = [];
        foreach ($commande->getRecettes() as $recette) {
            $recettes[] = [
                'id' => $recette->getId(),
                'nom' => $recette->getNom(),
                'temps_cuisson' => $recette->getTempsCuisson(),
                'description' => $recette->getDescription()
            ];
        }

        return $this->json(['commandes_recettes' => $recettes]);
    }

        #[Route('/{id}/ingredients', name: 'api_commande_ingredients', methods: ['GET'])]
    public function getCommandeIngredients(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $ingredients = [];
        foreach ($commande->getIngredients() as $ingredient) {
            $ingredients[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
                'quantite_stock' => $ingredient->getQuantiteStock(),
                'seuil_minimum' => $ingredient->getSeuilMinimum(),
            ];
        }

        return $this->json($ingredients);
    }
        private function mettreAJourStock(Commande $commande): void
        {
            foreach ($commande->getRecettes() as $recette) {
                foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                    $ingredient = $recetteIngredient->getIngredient();
                    $quantiteRequise = $recetteIngredient->getQuantite();
                    $ingredient->setQuantiteStock($ingredient->getQuantiteStock() - $quantiteRequise);
                }
            }
        }

        #[Route('/stock/reapprovisionner', name: 'api_stock_reapprovisionner', methods: ['POST'])]
        public function reapprovisionnerStock(Request $request, EntityManagerInterface $entityManager): JsonResponse
        {
            $data = json_decode($request->getContent(), true);
        
            if (!isset($data['ingredient_id']) || !isset($data['quantite_ajoutee'])) {
                return $this->json(['error' => 'Données invalides'], 400);
            }
        
            $ingredient = $entityManager->getRepository(Ingredient::class)->find($data['ingredient_id']);
        
            if (!$ingredient) {
                return $this->json(['error' => 'Ingrédient non trouvé'], 404);
            }   
        
            $ingredient->setQuantiteStock($ingredient->getQuantiteStock() + $data['quantite_ajoutee']);
        
            $entityManager->flush();
        
            return $this->json([
                'message' => 'Stock réapprovisionné avec succès',
                'ingredient' => [
                    'id' => $ingredient->getId(),
                    'nom' => $ingredient->getNom(),
                    'nouveau_stock' => $ingredient->getQuantiteStock(),
                ]
            ]);
        }
        
            #[Route('/{id}/cancel', name: 'api_commande_cancel', methods: ['DELETE'])]
            public function cancel(int $id, EntityManagerInterface $entityManager): JsonResponse
            {
                $commande = $entityManager->getRepository(Commande::class)->find($id);
            
                if (!$commande) {
                    return $this->json(['error' => 'Commande non trouvée'], 404);
                }
            
                // Restituer le stock si la commande était validée
                if ($commande->getStatus() === 'terminée') {
                    $this->restituerStock($commande);
                }
            
                // ✅ Supprimer les associations dans commande_recette
                foreach ($commande->getRecettes() as $recette) {
                    $commande->removeRecette($recette);
                }
            
                // ✅ Supprimer les associations dans commande_ingredient
                foreach ($commande->getIngredients() as $ingredient) {
                    $commande->removeIngredient($ingredient);
                }
            
                // Persist les modifications avant suppression
                $entityManager->flush();
            
                // ✅ Supprimer la commande
                $entityManager->remove($commande);
                $entityManager->flush();
            
                return $this->json(['message' => 'Commande annulée et stock rétabli si nécessaire']);
            }
            
            private function restituerStock(Commande $commande): void
            {
                foreach ($commande->getRecettes() as $recette) {
                    foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                        $ingredient = $recetteIngredient->getIngredient();
                        $quantiteRequise = $recetteIngredient->getQuantite();
                        $ingredient->setQuantiteStock($ingredient->getQuantiteStock() + $quantiteRequise);
                    }
                }
            }
            
       
            private function verifierStockDisponible(Commande $commande): bool
        {
            foreach ($commande->getRecettes() as $recette) {
                foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                    $ingredient = $recetteIngredient->getIngredient();
                    $quantiteRequise = $recetteIngredient->getQuantite();
        
                    if ($ingredient->getQuantiteStock() < $quantiteRequise) {
                        return false;
                    }
                }
            }
            return true;
        }
        #[Route('/stats', name: 'api_commande_stats', methods: ['GET'], priority: 2)]
public function getStatistics(EntityManagerInterface $entityManager): JsonResponse
{
    try {
        $commandeRepo = $entityManager->getRepository(Commande::class);

        // Nombre total de commandes
        $totalCommandes = $commandeRepo->count([]);

        // Commandes par statut
        $statuts = ['en attente', 'en préparation', 'livrée', 'annulée'];
        $commandesParStatut = [];
        foreach ($statuts as $statut) {
            $commandesParStatut[$statut] = $commandeRepo->count(['status' => $statut]);
        }

        // Montant total des ventes
        $queryMontant = $entityManager->createQuery(
            'SELECT SUM(c.montantTotal) FROM App\Entity\Commande c'
        );
        $montantTotal = $queryMontant->getSingleScalarResult() ?? 0;

        // Récupération de la dernière commande
        $queryLastCommande = $entityManager->createQuery(
            'SELECT c.createdAt FROM App\Entity\Commande c ORDER BY c.createdAt DESC'
        )->setMaxResults(1);
        
        $lastCommandeDate = $queryLastCommande->getSingleScalarResult();
        
        // Convertir en DateTimeImmutable si ce n'est pas déjà le cas
        if ($lastCommandeDate && is_string($lastCommandeDate)) {
            $lastCommandeDate = new \DateTimeImmutable($lastCommandeDate);
        }

        return $this->json([
            'total_commandes' => $totalCommandes,
            'commandes_par_statut' => $commandesParStatut,
            'montant_total' => number_format($montantTotal, 2) . " €",
            'derniere_commande' => $lastCommandeDate ? $lastCommandeDate->format('Y-m-d H:i:s') : 'Aucune commande',
        ]);
    } catch (\Exception $e) {
        return $this->json(['error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()], 500);
    }
}

}
