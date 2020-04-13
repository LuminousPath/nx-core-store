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

use App\Entity\FoodDatabase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method FoodDatabase|null find($id, $lockMode = null, $lockVersion = null)
 * @method FoodDatabase|null findOneBy(array $criteria, array $orderBy = null)
 * @method FoodDatabase[]    findAll()
 * @method FoodDatabase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodDatabaseRepository extends ServiceEntityRepository
{
    /**
     * FoodDatabaseRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodDatabase::class);
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
     * @param $food_info_id
     *
     * @return FoodDatabase|null Returns an array of FoodDatabase objects
     */
    public function findByFoodInfoId($food_info_id): ?FoodDatabase
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.remoteIds LIKE :val')
                ->setParameter('val', "%" . addcslashes($food_info_id, "%_") . "%")
                ->orderBy('f.id', 'ASC')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
