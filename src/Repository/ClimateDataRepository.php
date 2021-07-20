<?php

namespace App\Repository;

use App\Entity\ClimateData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClimateData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClimateData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClimateData[]    findAll()
 * @method ClimateData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClimateDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClimateData::class);
    }

    // /**
    //  * @return ClimateData[] Returns an array of ClimateData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClimateData
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
