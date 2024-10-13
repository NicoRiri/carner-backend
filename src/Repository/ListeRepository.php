<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Liste;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Liste>
 */
class ListeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Liste::class);
    }

    public function hasArticle(User $user, Article $article): bool
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery(
            'SELECT COUNT(l.id)
         FROM App\Entity\Liste l
         WHERE l.owner = :user
         AND l.article = :article'
        );
        $query->setParameter('user', $user);
        $query->setParameter('article', $article);

        return (int) $query->getSingleScalarResult() > 0;
    }

    //    /**
    //     * @return Liste[] Returns an array of Liste objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Liste
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
