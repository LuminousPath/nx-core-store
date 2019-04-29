<?php

namespace App\Repository;

use App\Entity\BodyBmi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BodyBmi|null find($id, $lockMode = null, $lockVersion = null)
 * @method BodyBmi|null findOneBy(array $criteria, array $orderBy = null)
 * @method BodyBmi[]    findAll()
 * @method BodyBmi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BodyBmiRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BodyBmi::class);
    }

    public function getLastReading( String $patientId ) {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('c.date_time', 'DEC')
            ->getQuery()
            ->getResult();
    }

    public function findByDate( String $patientId, String $date ) {
        $today = $date . " 00:00:00";

        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('c.date_time >= :val')
            ->setParameter('val', $today)
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('c.date_time', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return BodyBmi[] Returns an array of BodyBmi objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BodyBmi
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
