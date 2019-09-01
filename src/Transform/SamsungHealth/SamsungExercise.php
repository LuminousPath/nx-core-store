<?php

namespace App\Transform\SamsungHealth;

use App\AppConstants;
use App\Entity\Exercise;
use App\Entity\PartOfDay;
use App\Entity\Patient;
use App\Entity\PatientGoals;
use App\Entity\ThirdPartyService;
use App\Entity\TrackingDevice;
use App\Entity\UnitOfMeasurement;
use Doctrine\Common\Persistence\ManagerRegistry;

class SamsungExercise extends Constants
{
    /**
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @return Exercise|null
     */
    public static function translate(ManagerRegistry $doctrine, String $getContent)
    {
        $jsonContent = self::decodeJson($getContent);
        AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - : " . print_r($jsonContent, TRUE));

        if (property_exists($jsonContent, "uuid")) {
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

            /** @var PartOfDay $dataEntry */
            $partOfDay = self::getPartOfDay($doctrine, new \DateTime($jsonContent->dateTime));
            if (is_null($partOfDay)) {
                return NULL;
            }

            /** @var Exercise $dataEntry */
            $dataEntry = $doctrine->getRepository(Exercise::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'trackingDevice' => $deviceTracking]);
            if (!$dataEntry) {
                $dataEntry = new Exercise();
            }

//            $dataEntry->setPatient($patient);
//            $dataEntry->setPartOfDay($partOfDay);
//            $dataEntry->setRemoteId($jsonContent->remoteId);
//            if (is_null($dataEntry->getDateTime()) || $dataEntry->getDateTime()->format("U") <> (new \DateTime($jsonContent->dateTime))->format("U")) {
//                $dataEntry->setDateTime(new \DateTime($jsonContent->dateTime));
//            }
//            if (property_exists($jsonContent, "comment")) $dataEntry->setComment($jsonContent->comment);
//            $dataEntry->setMeasurement($jsonContent->measurement);
//            $dataEntry->setUnitOfMeasurement($unitOfMeasurement);
//            $dataEntry->setService($thirdPartyService);
//            $dataEntry->setTrackingDevice($deviceTracking);
//            $dataEntry->setPatientGoal($patientGoal);
//            if (is_null($deviceTracking->getLastSynced()) || $deviceTracking->getLastSynced()->format("U") < $dataEntry->getDateTime()->format("U")) {
//                $deviceTracking->setLastSynced($dataEntry->getDateTime());
//            }

            return $dataEntry;

        }

        return NULL;
    }
}