<?php

namespace App\Transform\SamsungHealth;

use App\AppConstants;
use App\Entity\FitCaloriesDailySummary;
use App\Entity\Patient;
use App\Entity\PatientGoals;
use App\Entity\ThirdPartyService;
use App\Entity\TrackingDevice;
use App\Service\AwardManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class SamsungCountDailyCalories extends Constants
{
    /**
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @param AwardManager    $awardManager
     *
     * @return FitCaloriesDailySummary|null
     */
    public static function translate(ManagerRegistry $doctrine, String $getContent, AwardManager $awardManager)
    {
        $jsonContent = self::decodeJson($getContent);
        //AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - : " . print_r($jsonContent, TRUE));

        if (property_exists($jsonContent, "uuid") && $jsonContent->device == 'VfS0qUERdZ') {
            ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - New call too FitCaloriesDailySummary for " . $jsonContent->remoteId);

            /** @var Patient $patient */
            $patient = self::getPatient($doctrine, $jsonContent->uuid);
            if (is_null($patient)) {
                return NULL;
            }

            /** @var ThirdPartyService $thirdPartyService */
            $thirdPartyService = self::getThirdPartyService($doctrine, self::SAMSUNGHEALTHSERVICE);
            if (is_null($thirdPartyService)) {
                return NULL;
            }

            /** @var TrackingDevice $deviceTracking */
            $deviceTracking = self::getTrackingDevice($doctrine, $patient, $thirdPartyService, $jsonContent->device);
            if (is_null($deviceTracking)) {
                return NULL;
            }

            /** @var PatientGoals $patientGoal */
            $patientGoal = self::getPatientGoal($doctrine, "FitCaloriesDailySummary", $jsonContent->goal, NULL, $patient);
            if (is_null($patientGoal)) {
                return NULL;
            }

            /** @var FitCaloriesDailySummary $dataEntry */
            $dataEntry = $doctrine->getRepository(FitCaloriesDailySummary::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'patient' => $patient, 'trackingDevice' => $deviceTracking]);
            if (!$dataEntry) {
                $dataEntry = new FitCaloriesDailySummary();
            }

            $dataEntry->setPatient($patient);
            $dataEntry->setTrackingDevice($deviceTracking);
            $dataEntry->setRemoteId($jsonContent->remoteId);
            $dataEntry->setValue($jsonContent->value);
            $dataEntry->setGoal($patientGoal);

            $dayStartTime = strtotime($jsonContent->dateTimeDayTime);
            $dayEndTime = strtotime(date("Y-m-d 23:59:59", $dayStartTime));
            $updateTime = strtotime($jsonContent->dateTimeUpdated) + (60*60);
            if ($updateTime > $dayEndTime) {
                $updateTime = $dayEndTime;
            }

            if (is_null($dataEntry->getDateTime()) || $dataEntry->getDateTime()->format("U") < $updateTime) {
                $dataEntry->setDateTime(new \DateTime(date("Y-m-d H:i:s", $updateTime)));
            }

            if (is_null($deviceTracking->getLastSynced()) || $deviceTracking->getLastSynced()->format("U") < $dataEntry->getDateTime()->format("U")) {
                $deviceTracking->setLastSynced($dataEntry->getDateTime());
            }

            try {
                $savedClassType = get_class($dataEntry);
                $savedClassType = str_ireplace("App\\Entity\\", "", $savedClassType);
                $updatedApi = self::updateApi($doctrine, $savedClassType, $patient, $thirdPartyService, $dataEntry->getDateTime());

                $entityManager = $doctrine->getManager();
                $entityManager->persist($updatedApi);
                $entityManager->flush();
            } catch (\Exception $e) {
                ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' ' . $e->getMessage());
            }

            return $dataEntry;

        }

        return NULL;
    }
}