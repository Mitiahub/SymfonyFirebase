<?php

namespace App\Repository;

use App\Entity\RecetteIngredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecetteIngredient>
 */
class RecetteIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecetteIngredient::class);
    }
        
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('ri')
            ->leftJoin('ri.recette', 'r')
            ->addSelect('r') // Inclure les données de la relation recette
            ->leftJoin('ri.ingredient', 'i')
            ->addSelect('i') // Inclure les données de la relation ingredient
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return RecetteIngredient[] Returns an array of RecetteIngredient objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RecetteIngredient
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
