<?php

namespace App\Repository;

use App\Entity\NoDatetimeData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NoDatetimeData|null find($id, $lockMode = null, $lockVersion = null)
 * @method NoDatetimeData|null findOneBy(array $criteria, array $orderBy = null)
 * @method NoDatetimeData[]    findAll()
 * @method NoDatetimeData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoDatetimeDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NoDatetimeData::class);
    }

    // /**
    //  * @return NoDatetimeData[] Returns an array of NoDatetimeData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NoDatetimeData
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
