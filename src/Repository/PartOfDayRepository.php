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

use App\Entity\PartOfDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PartOfDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method PartOfDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method PartOfDay[]    findAll()
 * @method PartOfDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartOfDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PartOfDay::class);
    }

    public function findOneByName($value): ?PartOfDay
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

//    /**
//     * @return PartOfDay[] Returns an array of PartOfDay objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PartOfDay
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
