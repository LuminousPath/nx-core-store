<?php
/**
 * This file is part of NxFIFTEEN Fitness Core.
 *
 * @link      https://nxfifteen.me.uk/projects/nx-health/store
 * @link      https://nxfifteen.me.uk/projects/nx-health/
 * @link      https://git.nxfifteen.rocks/nx-health/store
 * @author    Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @copyright Copyright (c) 2020. Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @license   https://nxfifteen.me.uk/api/license/mit/license.html MIT
 */

/** @noinspection DuplicatedCode */

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
    /**
     * FitStepsIntraDayRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FitStepsIntraDay::class);
    }

    /**
     * @param String $patientId
     * @param String $date
     * @param int    $trackingDevice
     *
     * @return mixed
     */
    public function findByDateRange(string $patientId, string $date, int $trackingDevice = 0)
    {
        return $this->findByDates($patientId, $date, "00:00:00", "23:59:00", $trackingDevice);
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
    public function findByDates(string $patientId, string $date, string $start, string $end, int $trackingDevice = 0)
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
     * @param int    $hour
     * @param int    $trackingDevice
     *
     * @return mixed
     */
    public function findByForHour(string $patientId, string $date, int $hour, int $trackingDevice = 0)
    {
        return $this->findByDates($patientId, $date, $hour . ":00:00", $hour . ":59:59", $trackingDevice);
    }

    /**
     * Find a Entity by its GUID
     *
     * @param string $value
     *
     * @return mixed
     */
    public function findByGuid(string $value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.guid = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
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
    public function findSumDates(string $patientId, string $date, string $start, string $end, int $trackingDevice = 0)
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
                ->select('sum(c.value) as sum')
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
                ->select('sum(c.value) as sum')
                ->getQuery()
                ->getResult();
        }
    }
}
