<?php

namespace App\Repository;

use App\Entity\DatetimeData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatetimeData|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatetimeData|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatetimeData[]    findAll()
 * @method DatetimeData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatetimeDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatetimeData::class);
    }

    // /**
    //  * @return DatetimeData[] Returns an array of DatetimeData objects
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
    public function findOneBySomeField($value): ?DatetimeData
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
