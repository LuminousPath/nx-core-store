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

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\RpgChallengeFriends;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RpgChallengeFriends|null find($id, $lockMode = null, $lockVersion = null)
 * @method RpgChallengeFriends|null findOneBy(array $criteria, array $orderBy = null)
 * @method RpgChallengeFriends[]    findAll()
 * @method RpgChallengeFriends[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RpgChallengeFriendsRepository extends ServiceEntityRepository
{
    /**
     * RpgChallengeFriendsRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RpgChallengeFriends::class);
    }

    /**
     * @param $patient Patient
     *
     * @return int
     */
    public function findChallengeWins($patient)
    {
        $challenger = $this->countChallengerByOutcome($patient, 5);
        $challenged = $this->countChallengedByOutcome($patient, 4);

        return $challenger + $challenged;
    }

    /**
     * @param $patient Patient
     *
     * @return int
     */
    public function findChallengeLoses($patient)
    {
        $challenger = $this->countChallengerByOutcome($patient, 4);
        $challenged = $this->countChallengedByOutcome($patient, 5);

        return $challenger + $challenged;
    }

    /**
     * @param $patient Patient
     *
     * @return int
     */
    public function findChallengeDraws($patient)
    {
        $challenger = $this->countChallengerByOutcome($patient, 6);
        $challenged = $this->countChallengedByOutcome($patient, 6);

        return $challenger + $challenged;
    }

    /**
     * @param Patient $patient
     * @param int     $int
     *
     * @return int
     */
    private function countChallengerByOutcome(Patient $patient, int $int)
    {
        return count($this->createQueryBuilder('c')
            ->andWhere('c.challenger = :patientId')
            ->setParameter('patientId', $patient->getId())
            ->andWhere('c.outcome = :winOutcome')
            ->setParameter('winOutcome', $int)
            ->getQuery()
            ->getResult());
    }

    /**
     * @param Patient $patient
     * @param int     $int
     *
     * @return int
     */
    private function countChallengedByOutcome(Patient $patient, int $int)
    {
        return count($this->createQueryBuilder('c')
            ->andWhere('c.challenged = :patientId')
            ->setParameter('patientId', $patient->getId())
            ->andWhere('c.outcome = :winOutcome')
            ->setParameter('winOutcome', $int)
            ->getQuery()
            ->getResult());
    }
}
