<?php

namespace App\Repository;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    // /**
    //  * @return Friend[] Returns an array of Friend objects
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
    public function findOneBySomeField($value): ?Friend
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function search($params, $value = null) {

        $QUERY = "SELECT friend.id FROM friend
            JOIN user
            WHERE user.username LIKE '%".$value."%'";

        $executes = [];
        foreach($params as $key=>$param) {
            $QUERY.=" AND ";
           if(str_contains($param, '.')) {
               $QUERY.= $key . " = " . $param;
           }else {
               $executes[] = $param;
               $QUERY.= $key . " = ?";
           }
        }

        $em = $this->getEntityManager();
        $statement = $em->getConnection()->prepare($QUERY);
        $statement->execute($executes);
        $results = $statement->fetchAll();


        $friends = [];
        foreach($results as $id) {
            $friends[] = $this->find($id);
        }

        return $friends;
    }
}
