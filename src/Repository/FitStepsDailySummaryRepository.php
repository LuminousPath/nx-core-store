<?php

namespace App\Repository;

use App\Entity\FitStepsDailySummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @method FitStepsDailySummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method FitStepsDailySummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method FitStepsDailySummary[]    findAll()
 * @method FitStepsDailySummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FitStepsDailySummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FitStepsDailySummary::class);
    }

    public function findSince(String $patientId, $dateSince)
    {
        /** @var \DateTime $dateSince */
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->andWhere('c.DateTime >= :startDate')
            ->setParameter('startDate', $dateSince->format("Y-m-d 00:00:00"))
            ->orderBy('c.value', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBetween(String $patientId, $dateSince, $dateTill)
    {
        /** @var \DateTime $dateSince */
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->andWhere('c.DateTime >= :startDate')
            ->setParameter('startDate', $dateSince->format("Y-m-d 00:00:00"))
            ->andWhere('c.DateTime <= :dateTill')
            ->setParameter('dateTill', $dateTill->format("Y-m-d 23:59:59"))
            ->orderBy('c.value', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findHighest(String $patientId, int $trackingDevice)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->andWhere('c.trackingDevice = :trackingDevice')
            ->setParameter('trackingDevice', $trackingDevice)
            ->orderBy('c.value', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param String $patientId
     * @param String $date
     * @param int    $lastDays
     * @param int    $trackingDevice
     *
     * @return mixed
     */
    public function findByDateRangeHistorical(String $patientId, String $date, int $lastDays = 0, int $trackingDevice = 0)
    {
        $dateObject = new \DateTime($date);

        if ($lastDays > 0) {
            try {
                $interval = new \DateInterval('P' . $lastDays . 'D');
                $dateObject->sub($interval);
                $today = $dateObject->format("Y-m-d") . " 00:00:00";
            } catch (\Exception $e) {
                $today = $date . " 00:00:00";
            }
        } else {
            $today = $date . " 00:00:00";
        }
        $todayEnd = $date . " 23:59:00";

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
    public function findByDateRange(String $patientId, String $date, int $trackingDevice)
    {
        return $this->findByDateRangeHistorical($patientId, $date, 0, $trackingDevice);
    }

}
