<?php

namespace App\Repository;

use App\Entity\FMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method FMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method FMessage[]    findAll()
 * @method FMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FMessage::class);
    }

    // /**
    //  * @return FMessage[] Returns an array of FMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FMessage
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
