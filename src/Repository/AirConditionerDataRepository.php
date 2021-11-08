<?php

namespace App\Repository;

use App\Entity\AirConditionerData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AirConditionerData|null find($id, $lockMode = null, $lockVersion = null)
 * @method AirConditionerData|null findOneBy(array $criteria, array $orderBy = null)
 * @method AirConditionerData[]    findAll()
 * @method AirConditionerData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AirConditionerDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AirConditionerData::class);
    }

    // /**
    //  * @return AirConditionerData[] Returns an array of AirConditionerData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AirConditionerData
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
