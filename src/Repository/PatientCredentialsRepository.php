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

use App\Entity\PatientCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PatientCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method PatientCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method PatientCredentials[]    findAll()
 * @method PatientCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatientCredentialsRepository extends ServiceEntityRepository
{
    /**
     * PatientCredentialsRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatientCredentials::class);
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
     * @param int $serviceId
     *
     * @return mixed
     */
    public function findExpired(int $serviceId)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.service', 's')
            ->andWhere('s.id = :patientId')
            ->setParameter('patientId', $serviceId)
            ->andWhere('a.expires < :currentTimestamp')
            ->setParameter('currentTimestamp', date("Y-m-d H:i:s"))
            ->getQuery()
            ->getResult();
    }
}
