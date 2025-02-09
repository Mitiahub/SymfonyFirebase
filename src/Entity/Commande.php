<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\CommandeRepository")]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $status = 'en attente';

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $montantTotal = '0.00';  // ✅ Correction du type float → string

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "commandes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToMany(targetEntity: Recette::class, inversedBy: "commandes")]
    #[ORM\JoinTable(name: "commande_recette")]
    private Collection $recettes;

    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: "commandes")]
    #[ORM\JoinTable(name: "commande_ingredient")]
    private Collection $ingredients;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->recettes = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getMontantTotal(): ?string  // ✅ Correction float → string
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(string $montantTotal): self
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt = null): static
    {
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->addCommande($this);
    
            // ✅ Ajout automatique des ingrédients liés à la recette
            foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                $ingredient = $recetteIngredient->getIngredient();
                if ($ingredient) {
                    $this->addIngredient($ingredient); // Ajout des ingrédients à la commande
                }
            }
        }
        return $this;
    }
    

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            $recette->removeCommande($this);  // ✅ Retrait de la relation inverse

            // Optionnel : Retirer les ingrédients si plus aucune recette ne les utilise
            foreach ($recette->getRecetteIngredients() as $recetteIngredient) {
                $this->removeIngredient($recetteIngredient->getIngredient());
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->addCommande($this);  // ✅ Ajout relation inverse
        }
        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->ingredients->removeElement($ingredient)) {
            $ingredient->removeCommande($this);  // ✅ Retrait relation inverse
        }
        return $this;
    }
}
