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

use App\Entity\NutritionInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NutritionInformation|null find($id, $lockMode = null, $lockVersion = null)
 * @method NutritionInformation|null findOneBy(array $criteria, array $orderBy = null)
 * @method NutritionInformation[]    findAll()
 * @method NutritionInformation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NutritionInformationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NutritionInformation::class);
    }

    public function findLastMeal($patient)
    {
        $em = $this->createQueryBuilder('n');
        return $em
            ->andWhere('n.patient = :patient')
            ->andWhere($em->expr()->isNotNull('n.calories'))
            ->setParameter('patient', $patient)
            ->orderBy('n.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function findLastWater($patient)
    {
        $em = $this->createQueryBuilder('n');
        return $em
            ->andWhere('n.patient = :patient')
            ->andWhere($em->expr()->isNotNull('n.water'))
            ->setParameter('patient', $patient)
            ->orderBy('n.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return NutritionInformation[] Returns an array of NutritionInformation objects
//     */
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
    public function findOneBySomeField($value): ?NutritionInformation
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