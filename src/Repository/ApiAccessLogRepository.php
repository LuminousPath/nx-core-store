<?php

/*
* This file is part of the Storage module in NxFIFTEEN Core.
*
* Copyright (c) 2019. Stuart McCulloch Anderson
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @package     Store
* @version     0.0.0.x
* @since       0.0.0.1
* @author      Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
* @link        https://nxfifteen.me.uk NxFIFTEEN
* @link        https://git.nxfifteen.rocks/nx-health NxFIFTEEN Core
* @link        https://git.nxfifteen.rocks/nx-health/store NxFIFTEEN Core Storage
* @copyright   2019 Stuart McCulloch Anderson
* @license     https://license.nxfifteen.rocks/mit/2015-2019/ MIT
*/

namespace App\Repository;

use App\Entity\ApiAccessLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ApiAccessLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiAccessLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiAccessLog[]    findAll()
 * @method ApiAccessLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiAccessLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ApiAccessLog::class);
    }

    public function findLastAccess($patient, $service, $entity): ?ApiAccessLog
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.patient = :patientId')
            ->setParameter('patientId', $patient)
            ->andWhere('a.thirdPartyService = :serviceId')
            ->setParameter('serviceId', $service)
            ->andWhere('a.entity = :val')
            ->setParameter('val', $entity)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
