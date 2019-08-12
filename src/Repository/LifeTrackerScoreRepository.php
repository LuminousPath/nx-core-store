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

use App\Entity\LifeTrackerScore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LifeTrackerScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method LifeTrackerScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method LifeTrackerScore[]    findAll()
 * @method LifeTrackerScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LifeTrackerScoreRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LifeTrackerScore::class);
    }

//    /**
//     * @return LifeTrackerScore[] Returns an array of LifeTrackerScore objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LifeTrackerScore
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}