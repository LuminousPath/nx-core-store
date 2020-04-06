<?php
/**
 * DONE This file is part of NxFIFTEEN Fitness Core.
 *
 * @link      https://nxfifteen.me.uk/projects/nx-health/store
 * @link      https://nxfifteen.me.uk/projects/nx-health/
 * @link      https://git.nxfifteen.rocks/nx-health/store
 * @author    Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @copyright Copyright (c) 2020. Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @license   https://nxfifteen.me.uk/api/license/mit/license.html MIT
 */

namespace App\Command;

use App\AppConstants;
use App\Entity\ApiAccessLog;
use App\Entity\FitStepsDailySummary;
use App\Entity\Patient;
use App\Entity\RpgChallengeFriends;
use App\Service\AwardManager;
use App\Service\TweetManager;
use DateInterval;
use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use MyBuilder\Bundle\CronosBundle\Annotation\Cron;
use phpDocumentor\Reflection\Types\Null_;
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
     * @var TweetManager
     */
    private $tweetManager;

    /**
     * @required
     *
     * @param ManagerRegistry $doctrine
     * @param AwardManager    $awardManager
     * @param TweetManager    $tweetManager
     */
    public function dependencyInjection(
        ManagerRegistry $doctrine,
        AwardManager $awardManager,
        TweetManager $tweetManager
    ): void
    {
        $this->doctrine = $doctrine;
        $this->awardManager = $awardManager;
        $this->tweetManager = $tweetManager;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
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
//                    $this->log("(" . $challenge->getId() . ") Challenge should finish early");
                    $challenge->setEndDate(new DateTime());
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

    private function updateEndDate(RpgChallengeFriends $challenge)
    {
        try {
            $endDate = new DateTime($challenge->getStartDate()->format("Y-m-d 00:00:00"));
            $endDate->add(new DateInterval("P" . $challenge->getDuration() . "D"));
//            $this->log("(" . $challenge->getId() . ") Challenge end date updated");
            $challenge->setEndDate($endDate);
        } catch (Exception $e) {
        }

        return $challenge;
    }

    private function log(string $msg)
    {
        AppConstants::writeToLog('debug_transform.txt', "[" . CronRpgChallengeFriends::$defaultName . "] - " . $msg);
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

        /** @var \DateTime[] $periods */
        $periods = new \DatePeriod(
            $challengeFriends->getStartDate(),
            new \DateInterval('P1D'),
            $challengeFriends->getEndDate()
        );
        foreach ($periods as $period) {
            $periodCriteria[$period->format("Y-m-d")] = 0;
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
        if (!is_null($apiLogLastSyncChallenger)) {
            $apiLogLastSyncChallenger = $apiLogLastSyncChallenger->getLastRetrieved()->format("U");
        }

        /** @var ApiAccessLog $apiLogLastSync */
        $apiLogLastSyncChallenged = $this->doctrine
            ->getRepository(ApiAccessLog::class)
            ->findOneBy(["patient" => $challenge->getChallenged(), "entity" => $challenge->getCriteria()]);
        if (!is_null($apiLogLastSyncChallenged)) {
            $apiLogLastSyncChallenged = $apiLogLastSyncChallenged->getLastRetrieved()->format("U");
        }

        $graceDate = $challenge->getEndDate();
        try {
            $graceDate->add(new DateInterval("P1D"));
        } catch (Exception $e) {
        }

        if (
            is_null($challenge->getCompletedAt()) &&
            ($challenge->getChallengerSum() >= $challenge->getTarget() || $challenge->getChallengedSum() >= $challenge->getTarget())
        ) {
            $challenge->setCompletedAt(new DateTime());
            $challenge->setEndDate(new DateTime());
        }

        if (date("U") >= $graceDate->format("U") ||
            is_null($apiLogLastSyncChallenger) ||
            is_null($apiLogLastSyncChallenged) ||
            (
                $apiLogLastSyncChallenger >= $challenge->getEndDate()->format("U") &&
                $apiLogLastSyncChallenged >= $challenge->getEndDate()->format("U")
            ) ||
            (
                $apiLogLastSyncChallenger >= $challenge->getCompletedAt()->format("U") &&
                $apiLogLastSyncChallenged >= $challenge->getCompletedAt()->format("U")
            )
        ) {
            $bothUpdated = TRUE;
        } else {
            $bothUpdated = FALSE;
        }

        if (
            ($challenge->getChallengerSum() >= $challenge->getTarget()) &&
            ($challenge->getChallengedSum() >= $challenge->getTarget())
        ) {
            $this->updateOutcomeDraw($challenge);
        } else if (
            ($challenge->getChallengerSum() >= $challenge->getTarget()) &&
            ($challenge->getChallengedSum() < $challenge->getTarget()) &&
            (
                $bothUpdated ||
                $apiLogLastSyncChallenged >= $apiLogLastSyncChallenger
            )
        ) {
            $this->updateOutcomeChallengerWin($challenge);
        } else if (
            ($challenge->getChallengerSum() < $challenge->getTarget()) &&
            ($challenge->getChallengedSum() >= $challenge->getTarget()) &&
            (
                $bothUpdated ||
                $apiLogLastSyncChallenger >= $apiLogLastSyncChallenged
            )
        ) {
            $this->updateOutcomeChallengerLose($challenge);
        } else if ($challenge->getChallengerSum() > $challenge->getChallengedSum() &&
            (
                $bothUpdated ||
                $apiLogLastSyncChallenged >= $apiLogLastSyncChallenger
            )
        ) {
            $this->updateOutcomeChallengerWin($challenge);
        } else if ($challenge->getChallengerSum() < $challenge->getChallengedSum() &&
            (
                $bothUpdated ||
                $apiLogLastSyncChallenger >= $apiLogLastSyncChallenged
            )
        ) {
            $this->updateOutcomeChallengerLose($challenge);
        } else if ($challenge->getChallengerSum() == $challenge->getChallengedSum() && $bothUpdated) {
            $this->updateOutcomeDraw($challenge);
        } else {
//            $this->log("(" . $challenge->getId() . ") Waiting on everyone to sync up");
            //$challenge->setOutcome(0);
        }

        return $challenge;
    }

    private function updateOutcomeDraw(RpgChallengeFriends $challenge)
    {
//        $this->log("(" . $challenge->getId() . ") It was a close thing that ended in a draw between " . $challenge->getChallenger()->getFirstName() . " and " . $challenge->getChallenged()->getFirstName());
        $challenge->setOutcome(6);
        $this->awardWinnerCreditTo($challenge->getChallenger());
        $this->awardWinnerCreditTo($challenge->getChallenged());

        $this->tweetManager->sendNotification(
            "@" . $challenge->getChallenger()->getUuid() . " and @" . $challenge->getChallenged()->getUuid() . " were too evenly matched!",
            "Their #challenge ended in a #draw",
            $challenge->getChallenger(),
            FALSE
        );

        $this->tweetManager->sendNotification(
            "It was a #draw. Your #challenge against @" . $challenge->getChallenged()->getUuid() . " was just too evenly matched",
            NULL,
            $challenge->getChallenger(),
            TRUE
        );

        $this->tweetManager->sendNotification(
            "It was a #draw. Your #challenge against @" . $challenge->getChallenger()->getUuid() . " was just too evenly matched",
            NULL,
            $challenge->getChallenged(),
            TRUE
        );

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
                    'challenged_pronoun' => $challenge->getChallenged()->getPronounThem(),
                    'challenge_outcome' => 'drawwin',
                    'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                    'challenge_duration' => $challenge->getDuration(),
                    'challenge_target' => number_format($challenge->getTarget()),
                    'relevant_url' => 'rpg/challenges',
                ]
            );

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
                    'challenged_pronoun' => $challenge->getChallenger()->getPronounThem(),
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
    }

    private function awardWinnerCreditTo(Patient $patient)
    {
        $this->awardManager->checkForAwards([], "pve", $patient);
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

    private function updateOutcomeChallengerWin(RpgChallengeFriends $challenge)
    {
//        $this->log("(" . $challenge->getId() . ") " . $challenge->getChallenger()->getFirstName() . " beat " . $challenge->getChallenged()->getFirstName() . " to reach " . $challenge->getTarget());
        $challenge->setOutcome(5);
        $this->awardWinnerCreditTo($challenge->getChallenger());

        $this->tweetManager->sendNotification(
            "It was close, but @" . $challenge->getChallenger()->getUuid() . " #beat @" . $challenge->getChallenged()->getUuid() . " :medal:",
            NULL,
            $challenge->getChallenger(),
            FALSE
        );

        $this->tweetManager->sendNotification(
            "You #won! You beat @" . $challenge->getChallenged()->getUuid() . " and claimed the gold :medal:",
            NULL,
            $challenge->getChallenger(),
            TRUE,
            "https://core.nxfifteen.me.uk/assets/badges/pve_1_1_winner.png"
        );

        $this->tweetManager->sendNotification(
            "It was so close, but you #lost this against @" . $challenge->getChallenger()->getUuid() . " this time :reminder_ribbon:",
            NULL,
            $challenge->getChallenged(),
            TRUE
        );

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
                    'challenged_pronoun' => $challenge->getChallenged()->getPronounThem(),
                    'challenge_outcome' => 'won',
                    'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                    'challenge_duration' => $challenge->getDuration(),
                    'challenge_target' => number_format($challenge->getTarget()),
                    'relevant_url' => 'rpg/challenges',
                ]
            );

            $this->awardManager->sendUserEmail(
                [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                'challenge_results',
                [
                    'html_title' => 'They think it\'s all over',
                    'header_image' => 'header5.png',
                    'patients_name' => $challenge->getChallenged()->getFirstName(),
                    'relevant_date' => date("F jS, Y"),
                    'challenged' => $challenge->getChallenger()->getFirstName(),
                    'challenged_pronoun' => $challenge->getChallenger()->getPronounThem(),
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
    }

    private function updateOutcomeChallengerLose(RpgChallengeFriends $challenge)
    {
//        $this->log("(" . $challenge->getId() . ") " . $challenge->getChallenged()->getFirstName() . " beat " . $challenge->getChallenger()->getFirstName() . " to reach " . $challenge->getTarget());
        $challenge->setOutcome(4);
        $this->awardWinnerCreditTo($challenge->getChallenged());

        $this->tweetManager->sendNotification(
            "It was close, but @" . $challenge->getChallenger()->getUuid() . " #beat @" . $challenge->getChallenged()->getUuid() . " :medal:",
            NULL,
            $challenge->getChallenger(),
            FALSE
        );

        $this->tweetManager->sendNotification(
            "You #won! You beat @" . $challenge->getChallenger()->getUuid() . " and claimed the gold :medal:",
            NULL,
            $challenge->getChallenger(),
            TRUE,
            "https://core.nxfifteen.me.uk/assets/badges/pve_1_1_winner.png"
        );

        $this->tweetManager->sendNotification(
            "It was so close, but you #lost this against @" . $challenge->getChallenged()->getUuid() . " this time :reminder_ribbon:",
            NULL,
            $challenge->getChallenger(),
            TRUE
        );

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
                    'challenged_pronoun' => $challenge->getChallenger()->getPronounThem(),
                    'challenge_outcome' => 'won',
                    'challenge_criteria' => $this->convertCriteriaEnglish($challenge->getCriteria()),
                    'challenge_duration' => $challenge->getDuration(),
                    'challenge_target' => number_format($challenge->getTarget()),
                    'relevant_url' => 'rpg/challenges',
                ]
            );

            $this->awardManager->sendUserEmail(
                [$challenge->getChallenger()->getEmail() => $challenge->getChallenger()->getFirstName() . ' ' . $challenge->getChallenger()->getSurName()],
                'challenge_results',
                [
                    'html_title' => 'They think it\'s all over',
                    'header_image' => 'header5.png',
                    'patients_name' => $challenge->getChallenger()->getFirstName(),
                    'relevant_date' => date("F jS, Y"),
                    'challenged' => $challenge->getChallenged()->getFirstName(),
                    'challenged_pronoun' => $challenge->getChallenged()->getPronounThem(),
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
    }

}
