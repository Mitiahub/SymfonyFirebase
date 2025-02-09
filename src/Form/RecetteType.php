<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\RecetteIngredient;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la recette',
            ])
            ->add('tempsCuisson', null, [
                'label' => 'Temps de cuisson (en minutes)',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('imageUrl', null, [
                'label' => 'URL de l’image',
            ])
            ->add('recetteIngredients', CollectionType::class, [
                'entry_type' => RecetteIngredientType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Ingrédients',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
