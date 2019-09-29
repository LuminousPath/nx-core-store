<?php
/**
 * Created by IntelliJ IDEA.
 * User: stuar
 * Date: 21/09/2019
 * Time: 08:51
 */

namespace App\Command;

use App\AppConstants;
use App\Entity\ApiAccessLog;
use App\Entity\FitStepsDailySummary;
use App\Entity\Patient;
use App\Entity\RpgChallengeFriends;
use App\Service\AwardManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use MyBuilder\Bundle\CronosBundle\Annotation\Cron;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Command for sending our email messages from the database.
 *
 * @Cron(minute="/30", noLogs=true, server="web")
 */
class CronRpgChallengeFriends extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'cron:rpg:challenge:friends';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var AwardManager
     */
    private $awardManager;

    /**
     * @required
     *
     * @param ManagerRegistry $doctrine
     * @param AwardManager    $awardManager
     */
    public function dependencyInjection(
        ManagerRegistry $doctrine,
        AwardManager $awardManager
    ): void
    {
        $this->doctrine = $doctrine;
        $this->awardManager = $awardManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var RpgChallengeFriends[] $challenges */
        $challenges = $this->doctrine
            ->getRepository(RpgChallengeFriends::class)
            ->findBy(['outcome' => NULL]);

        //$this->log("There are " . count($challenges) . " challenges still running");

        $entityManager = $this->doctrine->getManager();
        foreach ($challenges as $challenge) {
            if (!is_null($challenge->getStartDate())) {

                if (is_null($challenge->getEndDate())) {
                    $challenge = $this->updateEndDate($challenge);
                }

                $challengedValue = $this->queryDbForPatientCriteria($challenge, $challenge->getChallenged());
                $challenge->setChallengedSum(array_sum($challengedValue));
                $challenge->setChallengedDetails($challengedValue);

                $challengerValue = $this->queryDbForPatientCriteria($challenge, $challenge->getChallenger());
                $challenge->setChallengerSum(array_sum($challengerValue));
                $challenge->setChallengerDetails($challengerValue);

                if ($challenge->getChallengerSum() >= $challenge->getTarget() || $challenge->getChallengedSum() >= $challenge->getTarget()) {
                    $this->log("(" . $challenge->getId() . ") Challenge should finish early");
                    $challenge->setEndDate(new \DateTime());
                    $challenge = $this->updateOutcome($challenge);
                } else if (!is_null($challenge->getEndDate()) && $challenge->getEndDate()->format("U") < date("U")) {
                    $challenge = $this->updateOutcome($challenge);
                }

                //$this->log("(" . $challenge->getId() . ") Updated progress Challenger (" . $challenge->getChallengerSum() . ") / Challenged (" . $challenge->getChallengedSum() . ")");

                $entityManager->persist($challenge);
            }/* else {
                $this->log("(" . $challenge->getId() . ") Challenge hasn't been accepted yet");
            }*/
        }
        $entityManager->flush();
    }

    private function log(string $msg)
    {
        AppConstants::writeToLog('debug_transform.txt', "[" . CronRpgChallengeFriends::$defaultName . "] - " . $msg);
    }

    private function updateEndDate(RpgChallengeFriends $challenge)
    {
        $endDate = new \DateTime($challenge->getStartDate()->format("Y-m-d 00:00:00"));
        try {
            $endDate->add(new \DateInterval("P" . $challenge->getDuration() . "D"));
        } catch (\Exception $e) {
        }
        $this->log("(" . $challenge->getId() . ") Challenge end date updated");
        $challenge->setEndDate($endDate);

        return $challenge;
    }

    private function queryDbForPatientCriteria(RpgChallengeFriends $challengeFriends, Patient $user)
    {
        $product = [];
        $periodCriteria = [];

        switch ($challengeFriends->getCriteria()) {
            case "FitStepsDailySummary":
                /** @var FitStepsDailySummary[] $product */
                $product = $this->doctrine
                    ->getRepository(FitStepsDailySummary::class)
                    ->findBetween($user->getUuid(), $challengeFriends->getStartDate(), $challengeFriends->getEndDate());
                break;
        }

        foreach ($product as $dailySummary) {
            $periodCriteria[$dailySummary->getDateTime()->format("Y-m-d")] = $dailySummary->getValue();
        }

        ksort($periodCriteria);

        return $periodCriteria;
    }

    private function updateOutcome(RpgChallengeFriends $challenge)
    {
        /** @var ApiAccessLog $apiLogLastSync */
        $apiLogLastSyncChallenger = $this->doctrine
            ->getRepository(ApiAccessLog::class)
            ->findOneBy(["patient" => $challenge->getChallenger(), "entity" => $challenge->getCriteria()]);
        $apiLogLastSyncChallenger = $apiLogLastSyncChallenger->getLastRetrieved()->format("U");

        /** @var ApiAccessLog $apiLogLastSync */
        $apiLogLastSyncChallenged = $this->doctrine
            ->getRepository(ApiAccessLog::class)
            ->findOneBy(["patient" => $challenge->getChallenged(), "entity" => $challenge->getCriteria()]);
        $apiLogLastSyncChallenged = $apiLogLastSyncChallenged->getLastRetrieved()->format("U");

        $graceDate = new \DateTime($challenge->getEndDate()->format("Y-m-d 00:00:00"));
        try {
            $graceDate->add(new \DateInterval("P1D"));
        } catch (\Exception $e) {
        }

        if (date("U") >= $graceDate->format("U") || (
                $apiLogLastSyncChallenger >= $challenge->getEndDate()->format("U") &&
                $apiLogLastSyncChallenged >= $challenge->getEndDate()->format("U")
            )) {
            $bothUpdated = true;
        } else {
            $bothUpdated = false;
        }

        if (
            ($challenge->getChallengerSum() >= $challenge->getTarget()) &&
            ($challenge->getChallengedSum() >= $challenge->getTarget())
        ) {
            $this->log("(" . $challenge->getId() . ") It was a close thing that ended in a draw between " . $challenge->getChallenger()->getFirstName() . " and " . $challenge->getChallenged()->getFirstName());
            $challenge->setOutcome(6);
            $this->awardWinnerCreditTo($challenge->getChallenger());
            $this->awardWinnerCreditTo($challenge->getChallenged());

            try {
                $this->awardManager->sendUserEmail(
                    [
                        $challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName(),
                    ],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header6.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'drawwin',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
            try {
                $this->awardManager->sendUserEmail(
                    [
                        $challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName(),
                    ],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header6.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'drawwin',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }

        } else if (
            ($challenge->getChallengerSum() >= $challenge->getTarget()) &&
            ($challenge->getChallengedSum() < $challenge->getTarget()) &&
            $bothUpdated
        ) {
            $this->log("(" . $challenge->getId() . ") " . $challenge->getChallenger()->getFirstName() . " beat " . $challenge->getChallenged()->getFirstName() . " to reach " . $challenge->getTarget());
            $challenge->setOutcome(5);
            $this->awardWinnerCreditTo($challenge->getChallenger());

            // Email the winner
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header6.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'won',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
            // Email the loser
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header5.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'lost',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
        } else if (
            ($challenge->getChallengerSum() < $challenge->getTarget()) &&
            ($challenge->getChallengedSum() >= $challenge->getTarget()) &&
            $bothUpdated
        ) {
            $this->log("(" . $challenge->getId() . ") " . $challenge->getChallenged()->getFirstName() . " beat " . $challenge->getChallenger()->getFirstName() . " to reach " . $challenge->getTarget());
            $challenge->setOutcome(4);
            $this->awardWinnerCreditTo($challenge->getChallenged());

            // Email the winner
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header6.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'won',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
            // Email the loser
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header5.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'lost',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
        } else if ($challenge->getChallengerSum() > $challenge->getChallengedSum() && $bothUpdated) {
            $this->log("(" . $challenge->getId() . ") A moral victory for " . $challenge->getChallenger()->getFirstName() . " who beat " . $challenge->getChallenged()->getFirstName() . ", but couldn't reach the target of " . $challenge->getTarget());
            $challenge->setOutcome(3);
            AppConstants::awardPatientXP(
                $this->doctrine,
                $challenge->getChallenger(),
                10,
                "A moral victory for " . $challenge->getChallenger()->getFirstName() . " who beat " . $challenge->getChallenged()->getFirstName() . ", but couldn't reach the target of " . $challenge->getTarget(),
                new \DateTime()
            );

            // Email the winner
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header8.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'moral',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }

            // Email the loser
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header5.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'lost',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
        } else if ($challenge->getChallengerSum() < $challenge->getChallengedSum() && $bothUpdated) {
            $this->log("(" . $challenge->getId() . ") A moral victory for " . $challenge->getChallenged()->getFirstName() . " who beat " . $challenge->getChallenger()->getFirstName() . ", but couldn't reach the target of " . $challenge->getTarget());
            $challenge->setOutcome(2);
            AppConstants::awardPatientXP(
                $this->doctrine,
                $challenge->getChallenged(),
                10,
                "A moral victory for " . $challenge->getChallenged()->getFirstName() . " who beat " . $challenge->getChallenger()->getFirstName() . ", but couldn't reach the target of " . $challenge->getTarget(),
                new \DateTime()
            );

            // Email the winner
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header8.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'moral',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }

            // Email the loser
            try {
                $this->awardManager->sendUserEmail(
                    [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header5.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'lost',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }

        } else if ($challenge->getChallengerSum() == $challenge->getChallengedSum() && $bothUpdated) {
            $this->log("(" . $challenge->getId() . ") It's a tie between " . $challenge->getChallenged()->getFirstName() . " and " . $challenge->getChallenger()->getFirstName() . ", but nether could reach the target of " . $challenge->getTarget());
            $challenge->setOutcome(1);
            AppConstants::awardPatientXP(
                $this->doctrine,
                $challenge->getChallenged(),
                5,
                "It's a tie between " . $challenge->getChallenged()->getFirstName() . " and " . $challenge->getChallenger()->getFirstName() . ", but nether could reach the target of " . $challenge->getTarget(),
                new \DateTime()
            );
            AppConstants::awardPatientXP(
                $this->doctrine,
                $challenge->getChallenger(),
                5,
                "It's a tie between " . $challenge->getChallenged()->getFirstName() . " and " . $challenge->getChallenger()->getFirstName() . ", but nether could reach the target of " . $challenge->getTarget(),
                new \DateTime()
            );
            try {
                $this->awardManager->sendUserEmail(
                    [
                        $challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName(),
                    ],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header7.png',
                        'patients_name' => $challenge->getChallenger()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenged()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenged()->getPronounAlt(),
                        'challenge_outcome' => 'draw',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
            try {
                $this->awardManager->sendUserEmail(
                    [
                        $challenge->getChallenged()->getEmail() => $challenge->getChallenged()->getFirstName() . ' ' . $challenge->getChallenged()->getSurName(),
                    ],
                    'challenge_results',
                    [
                        'html_title' => 'They think it\'s all over',
                        'header_image' => 'header7.png',
                        'patients_name' => $challenge->getChallenged()->getFirstName(),
                        'relevant_date' => date("F jS, Y"),
                        'challenged' => $challenge->getChallenger()->getFirstName(),
                        'challenged_pronoun' => $challenge->getChallenger()->getPronounAlt(),
                        'challenge_outcome' => 'draw',
                        'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                        'challenge_duration' => $challenge->getDuration(),
                        'challenge_target' => number_format($challenge->getTarget()),
                        'relevant_url' => 'rpg/challenges',
                    ]
                );
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            }
        } else {
            $this->log("(" . $challenge->getId() . ") We should never get here in a challenge? I really don't know what happened");
            $challenge->setOutcome(0);
        }

        return $challenge;
    }

    private function awardWinnerCreditTo(Patient $patient)
    {
        $patient = $this->awardManager->giveBadge(
            $patient,
            [
                'patients_name' => $patient->getFirstName(),
                'html_title' => "Awarded the Step Target badge",
                'header_image' => '../badges/trg_steps_achieved_header.png',
                "dateTime" => new \DateTime(),
                'relevant_date' => (new \DateTime())->format("F jS, Y"),
                "name" => "PVP 1:1 Challenge",
                "repeat" => FALSE,
                'badge_name' => 'PVP 1:1 Challenge',
                'badge_xp' => 30,
                'badge_image' => 'pve_1_1_winner',
                'badge_text' => "You won the challenge",
                'badge_longtext' => "They didn't stand a chance against you!",
                'badge_citation' => "They didn't stand a chance against you!",
            ]
        );

        return $patient;
    }

    private function convertCriteriaEnglish($criteria)
    {
        switch ($criteria) {
            case "FitStepsDailySummary":
                return "steps";
                break;
            default:
                return $criteria;
                break;
        }
    }

}