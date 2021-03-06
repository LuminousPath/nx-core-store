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

use App\Entity\BodyFat;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;

/**
 * @method BodyFat|null find($id, $lockMode = null, $lockVersion = null)
 * @method BodyFat|null findOneBy(array $criteria, array $orderBy = null)
 * @method BodyFat[]    findAll()
 * @method BodyFat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BodyFatRepository extends ServiceEntityRepository
{
    /**
     * BodyFatRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BodyFat::class);
    }

    /**
     * @param String $patientId
     * @param String $date
     *
     * @return mixed
     * @throws Exception
     */
    public function findByDateRange(string $patientId, string $date)
    {
        return $this->findSingleDate($patientId, $date);
    }

    /**
     * @param String $patientId
     * @param String $date
     *
     * @return mixed
     * @throws Exception
     */
    public function findSingleDate(string $patientId, string $date)
    {
        $today = $date . " 00:00:00";
        $todayEnd = $date . " 23:59:00";

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

    /**
     * @param String $patientId
     * @param String $date
     * @param int    $lastDays
     *
     * @return mixed
     * @throws Exception
     */
    public function findByDateRangeHistorical(string $patientId, string $date, int $lastDays)
    {
        $dateObject = new DateTime($date);

        try {
            $interval = new DateInterval('P' . $lastDays . 'D');
            $dateObject->sub($interval);
            $today = $dateObject->format("Y-m-d") . " 00:00:00";
        } catch (Exception $e) {
            $today = $date . " 00:00:00";
        }
        $todayEnd = $date . " 23:59:00";

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
     *
     * @return mixed
     */
    public function findFirst(string $patientId)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('c.DateTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }
}
