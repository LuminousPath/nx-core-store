<?php

namespace App\Transform\SamsungHealth;

use App\AppConstants;
use App\Entity\BodyWeight;
use App\Entity\PartOfDay;
use App\Entity\Patient;
use App\Entity\PatientGoals;
use App\Entity\ThirdPartyService;
use App\Entity\TrackingDevice;
use App\Entity\UnitOfMeasurement;
use App\Service\AwardManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class SamsungBodyWeight extends Constants
{
    /**
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @param AwardManager    $awardManager
     *
     * @return BodyWeight|null
     */
    public static function translate(ManagerRegistry $doctrine, String $getContent, AwardManager $awardManager)
    {
        $jsonContent = self::decodeJson($getContent);
        //AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - : " . print_r($jsonContent, TRUE));

        if (property_exists($jsonContent, "uuid")) {
            ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - New call too BodyWeight for " . $jsonContent->remoteId);

            /** @var Patient $patient */
            $patient = self::getPatient($doctrine, $jsonContent->uuid);
            if (is_null($patient)) {
                return NULL;
            }

            if (self::startsWith($jsonContent->comment,"Fitbit: ") && $jsonContent->device == "yJdRrYpC43") {
                /** @var ThirdPartyService $thirdPartyService */
                $thirdPartyService = self::getThirdPartyService($doctrine, self::FITBITSERVICE);
            } else {
                /** @var ThirdPartyService $thirdPartyService */
                $thirdPartyService = self::getThirdPartyService($doctrine, self::SAMSUNGHEALTHSERVICE);
            }
            if (is_null($thirdPartyService)) {
                return NULL;
            }

            if (self::startsWith($jsonContent->comment,"Fitbit: ") && $jsonContent->device == "yJdRrYpC43") {
                /** @var TrackingDevice $deviceTracking */
                $deviceTracking = self::getTrackingDevice($doctrine, $patient, $thirdPartyService, "Aria");
            } else {
                /** @var TrackingDevice $deviceTracking */
                $deviceTracking = self::getTrackingDevice($doctrine, $patient, $thirdPartyService, $jsonContent->device);
            }
            if (is_null($deviceTracking)) {
                return NULL;
            }

            /** @var PartOfDay $partOfDay */
            $partOfDay = self::getPartOfDay($doctrine, new \DateTime($jsonContent->dateTime));
            if (is_null($partOfDay)) {
                return NULL;
            }

            /** @var UnitOfMeasurement $unitOfMeasurement */
            $unitOfMeasurement = self::getUnitOfMeasurement($doctrine, $jsonContent->weightUnitOfMeasurement);
            if (is_null($unitOfMeasurement)) {
                return NULL;
            }

            /** @var PatientGoals $patientGoal */
            $patientGoal = self::getPatientGoal($doctrine, "BodyWeight", $jsonContent->weightGoal, $unitOfMeasurement, $patient);
            if (is_null($patientGoal)) {
                return NULL;
            }

            /** @var BodyWeight $dataEntry */
            $dataEntry = $doctrine->getRepository(BodyWeight::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'patient' => $patient, 'trackingDevice' => $deviceTracking]);
            if (!$dataEntry) {
                $dataEntry = new BodyWeight();
            }

            $dataEntry->setPatient($patient);

            $dataEntry->setTrackingDevice($deviceTracking);
            $dataEntry->setRemoteId($jsonContent->remoteId);
            $dataEntry->setMeasurement($jsonContent->weightMeasurement);
            $dataEntry->setUnitOfMeasurement($unitOfMeasurement);
            $dataEntry->setPatientGoal($patientGoal);
            if (is_null($dataEntry->getDateTime()) || $dataEntry->getDateTime()->format("U") <> (new \DateTime($jsonContent->dateTime))->format("U")) {
                $dataEntry->setDateTime(new \DateTime($jsonContent->dateTime));
            }
            $dataEntry->setPartOfDay($partOfDay);
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