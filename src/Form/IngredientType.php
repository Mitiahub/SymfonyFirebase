<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'ingrédient',
                'attr' => [
                    'placeholder' => 'Exemple : Tomate',
                ],
            ])
            ->add('quantiteStock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'constraints' => [
                    new GreaterThanOrEqual(0, null, [
                        'message' => 'La quantité en stock ne peut pas être négative.',
                    ]),
                ],
            ])
            ->add('seuilMinimum', IntegerType::class, [
                'label' => 'Seuil minimum',
                'constraints' => [
                    new GreaterThanOrEqual(0, null, [
                        'message' => 'Le seuil minimum ne peut pas être négatif.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Exemple : 10',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}
