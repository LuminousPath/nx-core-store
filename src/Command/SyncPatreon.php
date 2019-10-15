<?php
/**
 * This file is part of NxFIFTEEN Fitness Core.
 *
 * Copyright (c) 2019. Stuart McCulloch Anderson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\AppConstants;
use App\Entity\Patient;
use App\Entity\PatientCredentials;
use App\Entity\PatientMembership;
use App\Entity\PatientSettings;
use App\Entity\SyncQueue;
use App\Entity\ThirdPartyService;
use App\Service\AwardManager;
use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use MyBuilder\Bundle\CronosBundle\Annotation\Cron;
use Patreon\API;
use Patreon\Exceptions\APIException;
use Patreon\Exceptions\CurlException;
use Psr\Log\LoggerInterface;
use SodiumException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for sending our email messages from the database.
 *
 * @Cron(minute="/90", noLogs=true, server="web")
 */
class SyncPatreon extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'queue:fetch:patreon';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AwardManager
     */
    private $awardManager;
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @required
     *
     * @param ManagerRegistry $doctrine
     * @param LoggerInterface $logger
     * @param AwardManager    $awardManager
     */
    public function dependencyInjection(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        AwardManager $awardManager
    ): void
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->awardManager = $awardManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' Refreshing Patreon\'s');
        $this->syncServicePatreon();
    }

    private function syncServicePatreon()
    {
        /** @var ThirdPartyService $service */
        $service = AppConstants::getThirdPartyService($this->doctrine, "Patreon");

        /** @var SyncQueue[] $patientCredentials */
        $patreonCredentials = $this->doctrine
            ->getRepository(PatientCredentials::class)
            ->findBy(['service' => $service]);

        if (count($patreonCredentials) > 0) {
            foreach ($patreonCredentials as $patreonCredential) {
                AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' Refreshing Patreon ' . $patreonCredential->getPatient()->getFirstName());
                $api_client = new API($patreonCredential->getToken());

                try {
                    $patron_user = $api_client->fetch_user();
                    if (array_key_exists("included", $patron_user) && array_key_exists("0", $patron_user['included']) && array_key_exists("attributes", $patron_user['included'][0])) {
                        $this->entityManager = $this->doctrine->getManager();
                        $endPointTearSettings = $this->updateUserTear($patron_user['included'][0]['attributes'], $patreonCredential->getPatient(), $service);
                        $endPointStatusSettings = $this->updateUserStatus($patron_user['included'][0]['attributes'], $patreonCredential->getPatient(), $service);

                        $this->checkMembershipTear($endPointTearSettings, $endPointStatusSettings, $patreonCredential->getPatient());

                        $this->entityManager->flush();
                    }
                } catch (APIException $e) {
                } catch (CurlException $e) {
                } catch (SodiumException $e) {
                }
            }
        }
    }

    /**
     * @param array             $patron_user
     * @param Patient           $patient
     * @param ThirdPartyService $service
     *
     * @return PatientSettings
     */
    private function updateUserTear(array $patron_user, Patient $patient, ThirdPartyService $service)
    {
        if (array_key_exists("currently_entitled_amount_cents", $patron_user)) {
            $currently_entitled_amount_cents = $patron_user['currently_entitled_amount_cents'];
        } else {
            $currently_entitled_amount_cents = -1;
        }

        if (array_key_exists("lifetime_support_cents", $patron_user)) {
            $lifetime_support_cents = $patron_user['lifetime_support_cents'];
        } else {
            $lifetime_support_cents = -1;
        }

        // Tear Settings
        /** @var PatientSettings $endPointTearSettings */
        $endPointTearSettings = $this->doctrine
            ->getRepository(PatientSettings::class)
            ->findOneBy(['service' => $service, 'patient' => $patient, 'name' => 'tearPaid']);
        if (!$endPointTearSettings) {
            $endPointTearSettings = new PatientSettings();
            $endPointTearSettings->setPatient($patient);
            $endPointTearSettings->setService($service);
            $endPointTearSettings->setName("tearPaid");
        }

        $endPointTearSettings->setValue([
            $currently_entitled_amount_cents,
            $lifetime_support_cents,
        ]);

        $this->entityManager->persist($endPointTearSettings);

        return $endPointTearSettings;
    }

    /**
     * @param array             $patron_user
     * @param Patient           $patient
     * @param ThirdPartyService $service
     *
     * @return PatientSettings
     */
    private function updateUserStatus(array $patron_user, Patient $patient, ThirdPartyService $service)
    {
        if (array_key_exists("last_charge_status", $patron_user)) {
            $last_charge_status = $patron_user['last_charge_status'];
        } else {
            $last_charge_status = 'unknown';
        };
        if (array_key_exists("last_charge_date", $patron_user)) {
            $last_charge_date = $patron_user['last_charge_date'];
        } else {
            $last_charge_date = '';
        };
        if (array_key_exists("patron_status", $patron_user)) {
            $patron_status = $patron_user['patron_status'];
        } else {
            $patron_status = 'unknown';
        };
        if (array_key_exists("pledge_relationship_start", $patron_user)) {
            $pledge_relationship_start = $patron_user['pledge_relationship_start'];
        } else {
            $pledge_relationship_start = '';
        };

        // Patreon Status
        /** @var PatientSettings $endPointTearSettings */
        $endPointStatusSettings = $this->doctrine
            ->getRepository(PatientSettings::class)
            ->findOneBy(['service' => $service, 'patient' => $patient, 'name' => 'tearStatus']);
        if (!$endPointStatusSettings) {
            $endPointStatusSettings = new PatientSettings();
            $endPointStatusSettings->setPatient($patient);
            $endPointStatusSettings->setService($service);
            $endPointStatusSettings->setName("tearStatus");
        }

        $endPointStatusSettings->setValue([
            $patron_status,
            $last_charge_status,
            $last_charge_date,
            $pledge_relationship_start,
        ]);

        $this->entityManager->persist($endPointStatusSettings);

        return $endPointStatusSettings;
    }

    /**
     * @param PatientSettings $endPointTearSettings
     * @param PatientSettings $endPointStatusSettings
     * @param Patient         $patient
     */
    private function checkMembershipTear(PatientSettings $endPointTearSettings, PatientSettings $endPointStatusSettings, Patient $patient)
    {
        $settingsTear = $endPointTearSettings->getValue();
        $settingsStatus = $endPointStatusSettings->getValue();

        $patientMembership = $patient->getMembership();
        if (is_null($patientMembership)) {
            $patientMembership = new PatientMembership();
            $patientMembership->setPatient($patient);
            $patientMembership->setLifetime(FALSE);
            $patientMembership->setActive(FALSE);
            $patientMembership->setSince(new DateTime());
            $patientMembership->setLastPaid(new DateTime('1900-01-01 00:00:00'));
        }

        $patientMembership->setTear($this->getTearFromPaidCent($settingsTear[0]));
        if (strtolower($settingsStatus[1]) == "paid") {
            $patientMembership->setActive(TRUE);
        } else {
            $patientMembership->setActive(FALSE);
        }
        try {
            $patientMembership->setSince(new DateTime($settingsStatus[3]));
            $patientMembership->setLastPaid(new DateTime($settingsStatus[2]));
        } catch (Exception $e) {
        }

        $this->awardMembersBadge($patientMembership);

        $this->entityManager->persist($patientMembership);
    }

    /**
     * @param int $latestPaid
     *
     * @return string
     */
    private
    function getTearFromPaidCent(int $latestPaid)
    {
        switch ($latestPaid) {
            case "100":
                return "shoutout";
            case "500":
                return "supporter";
            case "1000":
                return "yearly_history";
            case "1500":
                return "all_history";
        }
    }

    private function awardMembersBadge(PatientMembership $patientMembership)
    {
        $this->awardManager->giveBadge(
            $patientMembership->getPatient(),
            [
                'patients_name' => $patientMembership->getPatient()->getFirstName(),
                'html_title' => "Patreon",
                'header_image' => '../badges/patreon_header.png',
                "dateTime" => $patientMembership->getLastPaid(),
                'relevant_date' => $patientMembership->getLastPaid()->format("F jS, Y"),
                "name" => "patreon name",
                "repeat" => FALSE,
                'badge_name' => 'patreon badge_name',
                'badge_xp' => 0,
                'badge_image' => 'patreon',
                'badge_text' => "patreon badge_text",
                'badge_longtext' => "patreon badge_longtext",
                'badge_citation' => "patreon badge_citation",
            ]
        );
    }

}
