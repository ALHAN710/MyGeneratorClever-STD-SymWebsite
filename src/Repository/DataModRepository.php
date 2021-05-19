<?php

namespace App\Repository;

use App\Entity\DataMod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataMod|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataMod|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataMod[]    findAll()
 * @method DataMod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataModRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataMod::class);
    }

    // /**
    //  * @return DataMod[] Returns an array of DataMod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataMod
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
