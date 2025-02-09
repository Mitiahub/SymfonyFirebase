<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recette', EntityType::class, [
                'class' => Recette::class,
                'choice_label' => 'nom', // Affiche le nom de la recette dans le champ
                'label' => 'Recette',
            ])
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'nom', // Affiche le nom de l'ingrédient dans le champ
                'label' => 'Ingrédient',
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'min' => 1, // Quantité minimale de 1
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecetteIngredient::class,
        ]);
    }
}
