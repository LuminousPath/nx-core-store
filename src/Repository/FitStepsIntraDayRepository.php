<?php

namespace App\Repository;

use App\Entity\FitStepsIntraDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FitStepsIntraDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method FitStepsIntraDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method FitStepsIntraDay[]    findAll()
 * @method FitStepsIntraDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FitStepsIntraDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FitStepsIntraDay::class);
    }

    /**
     * @param String $patientId
     * @param String $date
     * @param String $start
     * @param String $end
     * @param int    $trackingDevice
     *
     * @return mixed
     */
    public function findByDates(String $patientId, String $date, String $start, String $end, int $trackingDevice = 0)
    {
        $today = $date . " " . $start;
        $todayEnd = $date . " " . $end;

        if ($trackingDevice > 0) {
            return $this->createQueryBuilder('c')
                ->leftJoin('c.patient', 'p')
                ->andWhere('c.trackingDevice = :trackingDevice')
                ->setParameter('trackingDevice', $trackingDevice)
                ->andWhere('c.DateTime >= :val')
                ->setParameter('val', $today)
                ->andWhere('c.DateTime <= :valEnd')
                ->setParameter('valEnd', $todayEnd)
                ->andWhere('p.uuid = :patientId')
                ->setParameter('patientId', $patientId)
                ->orderBy('c.DateTime', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('c')
                ->leftJoin('c.patient', 'p')
                ->andWhere('c.DateTime >= :val')
                ->setParameter('val', $today)
                ->andWhere('c.DateTime <= :valEnd')
                ->setParameter('valEnd', $todayEnd)
                ->andWhere('p.uuid = :patientId')
                ->setParameter('patientId', $patientId)
                ->orderBy('c.DateTime', 'ASC')
                ->getQuery()
                ->getResult();
        }
    }

    /**
     * @param String $patientId
     * @param String $date
     * @param int    $trackingDevice
     *
     * @return mixed
     */
    public function findByDateRange(String $patientId, String $date, int $trackingDevice = 0)
    {
        return $this->findByDates($patientId, $date, "00:00:00", "23:59:00", $trackingDevice);
    }
}
