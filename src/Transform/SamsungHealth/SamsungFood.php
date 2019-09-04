<?php

namespace App\Transform\SamsungHealth;

use App\AppConstants;
use App\Entity\FoodDatabase;
use App\Entity\FoodDiary;
use App\Entity\FoodMeals;
use App\Entity\FoodNutrition;
use App\Entity\Patient;
use App\Entity\ThirdPartyService;
use App\Entity\TrackingDevice;
use App\Entity\UnitOfMeasurement;
use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;

class SamsungFood extends Constants
{

    /**
     * food_intake
     *
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @return FoodDiary|null
     */
    public static function translateFoodIntake(ManagerRegistry $doctrine, String $getContent)
    {
        $jsonContent = self::decodeJson($getContent);
        //AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - translateFoodIntake : " . print_r($jsonContent, TRUE));

        if (property_exists($jsonContent, "uuid") &&
            property_exists($jsonContent, "name") &&
            !empty($jsonContent->food_info_id) &&
            $jsonContent->food_info_id != "meal_removed" &&
            $jsonContent->food_info_id != "meal_auto_filled") {
            ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - New call too FoodIntake for " . $jsonContent->remoteId);

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

            /** @var UnitOfMeasurement $unitOfMeasurement */
            if (property_exists($jsonContent, "unit")) {
                $unitOfMeasurement = self::getUnitOfMeasurement($doctrine, self::convertMealUnit($jsonContent->unit));
                if (is_null($unitOfMeasurement)) {
                    return NULL;
                }
            } else {
                $unitOfMeasurement = NULL;
            }

            /** @var FoodMeals $mealType */
            $mealType = self::getMealType($doctrine, self::convertMeal($jsonContent->meal_type));
            if (is_null($mealType)) {
                return NULL;
            }

            /** @var FoodDatabase $mealFoodItem */
            $mealFoodItem = self::getMealFoodItem($doctrine, $jsonContent->food_info_id);
            if (is_null($mealFoodItem)) {
                return NULL;
            }

            /** @var FoodDiary $dataEntry */
            $dataEntry = $doctrine->getRepository(FoodDiary::class)->findOneBy(['remoteId' => $jsonContent->remoteId, 'patient' => $patient]);
            if (!$dataEntry) {
                $dataEntry = new FoodDiary();
            }

            $dataEntry->setRemoteId($jsonContent->remoteId);

            if (is_null($dataEntry->getDateTime()) || $dataEntry->getDateTime()->format("U") <> (new \DateTime($jsonContent->dateTime))->format("U")) {
                $dataEntry->setDateTime(new \DateTime($jsonContent->dateTime));
            }

            $dataEntry->setMeal($mealType);
            $dataEntry->setPatient($patient);
            $dataEntry->setTrackingDevice($deviceTracking);
            if (property_exists($jsonContent, "amount") && $jsonContent->amount > 0) $dataEntry->setAmount($jsonContent->amount);
            $dataEntry->setFoodItem($mealFoodItem);
            if (property_exists($jsonContent, "comment") && !empty($jsonContent->comment)) $dataEntry->setComment($jsonContent->comment);
            $dataEntry->setUnit($unitOfMeasurement);

            try {
                $savedClassType = "FoodIntake";
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

    /**
     * food_info
     *
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @return FoodDatabase|FoodNutrition|null
     */
    public static function translateFoodInfo(ManagerRegistry $doctrine, String $getContent)
    {
        $jsonContent = self::decodeJson($getContent);
        //AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - translateFoodInfo : " . print_r($jsonContent, TRUE));

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

            if (property_exists($jsonContent, "name") && !empty($jsonContent->name) && self::startsWith($jsonContent->name, "MyFitnessPal")) {
                ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - New call too FoodDatabase for " . $jsonContent->remoteId);

                try {
                    $savedClassType = "FoodDatabase";
                    $savedClassType = str_ireplace("App\\Entity\\", "", $savedClassType);
                    $updatedApi = self::updateApi($doctrine, $savedClassType, $patient, $thirdPartyService, new DateTime());

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($updatedApi);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' ' . $e->getMessage());
                }

                $jsonContent->title = $jsonContent->name;
                $jsonContent->remoteId = $jsonContent->provider_food_id;

                switch ($jsonContent->name) {
                    case "MyFitnessPal Breakfast":
                        $jsonContent->meal_type = 100001;
                        break;
                    case "MyFitnessPal Lunch":
                        $jsonContent->meal_type = 100002;
                        break;
                    case "MyFitnessPal Dinner":
                        $jsonContent->meal_type = 100003;
                        break;
                    case "MyFitnessPal Snacks":
                        $jsonContent->meal_type = 900001;
                        break;
                }

                if (preg_match('/^(?P<meal_id>[\d]+)-(?P<date>[\w ]+) 00:00:00 GMT([+\-]\d+:\d+|) (?P<year>[\d]+)/m', $jsonContent->remoteId, $regs)) {
                    $jsonContent->meal_type = $regs['meal_id'];
                    $jsonContent->dateTime = $regs['date'] . " " . $regs['year'];
                } else {
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - " . $jsonContent->remoteId);
                    $jsonContent->dateTime = str_ireplace($jsonContent->meal_type . "-", "", $jsonContent->remoteId);
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - " . $jsonContent->dateTime);
                    $jsonContent->dateTime = str_ireplace("GMT+01:00 ", "", $jsonContent->dateTime);
                    $jsonContent->dateTime = str_ireplace("GMT ", "", $jsonContent->dateTime);
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - " . $jsonContent->dateTime);
                    $jsonContent->dateTime = date('Y-m-d H:i:s', strtotime($jsonContent->dateTime));
                }
                ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - " . $jsonContent->remoteId);
                ///AppConstants::writeToLog('debug_transform.txt', "    - " . $jsonContent->meal_type);
                ///AppConstants::writeToLog('debug_transform.txt', "    - " . $jsonContent->dateTime);

                return self::translateFood($doctrine, self::encodeJson($jsonContent));
            } else {
                /** @var UnitOfMeasurement $unitOfMeasurement */
                if (property_exists($jsonContent, "metric_serving_unit")) {
                    $unitOfMeasurement = self::getUnitOfMeasurement($doctrine, $jsonContent->metric_serving_unit);
                    if (is_null($unitOfMeasurement)) {
                        return NULL;
                    }
                } else {
                    $unitOfMeasurement = NULL;
                }

                /** @var FoodDatabase $dataEntry */
                $dataEntry = $doctrine->getRepository(FoodDatabase::class)->findOneBy(['providerId' => $jsonContent->provider_food_id, 'service' => $thirdPartyService]);
                if (!$dataEntry) {
                    $dataEntry = new FoodDatabase();
                }

                $dataEntry->setProviderId($jsonContent->provider_food_id);
                $dataEntry->setServingUnit($unitOfMeasurement);
                $dataEntry->setService($thirdPartyService);

                if (property_exists($jsonContent, "calorie") && $jsonContent->calorie > 0) $dataEntry->setCalorie($jsonContent->calorie);
                if (property_exists($jsonContent, "carbohydrate") && $jsonContent->carbohydrate > 0) $dataEntry->setCarbohydrate($jsonContent->carbohydrate);
                if (property_exists($jsonContent, "dietary_fiber") && $jsonContent->dietary_fiber > 0) $dataEntry->setDietaryFiber($jsonContent->dietary_fiber);
                if (property_exists($jsonContent, "name") && !empty($jsonContent->name)) $dataEntry->setName($jsonContent->name);
                if (property_exists($jsonContent, "protein") && $jsonContent->protein > 0) $dataEntry->setProtein($jsonContent->protein);
                if (property_exists($jsonContent, "saturated_fat") && $jsonContent->saturated_fat > 0) $dataEntry->setSaturatedFat($jsonContent->saturated_fat);
                if (property_exists($jsonContent, "metric_serving_amount") && $jsonContent->metric_serving_amount > 0) $dataEntry->setServingAmount($jsonContent->metric_serving_amount);
                if (property_exists($jsonContent, "serving_description") && !empty($jsonContent->serving_description)) $dataEntry->setServingDescription($jsonContent->serving_description);
                if (property_exists($jsonContent, "default_number_of_serving_unit") && $jsonContent->default_number_of_serving_unit > 0) $dataEntry->setServingNumberDefault($jsonContent->default_number_of_serving_unit);
                if (property_exists($jsonContent, "sugar") && $jsonContent->sugar > 0) $dataEntry->setSugar($jsonContent->sugar);
                if (property_exists($jsonContent, "total_fat") && $jsonContent->total_fat > 0) $dataEntry->setTotalFat($jsonContent->total_fat);

                $currentRemoteIds = $dataEntry->getRemoteIds();
                if (!in_array($jsonContent->remoteId, $currentRemoteIds)) {
                    array_push($currentRemoteIds, $jsonContent->remoteId);
                    $dataEntry->setRemoteIds($currentRemoteIds);
                }

                try {
                    $savedClassType = "FoodDatabase";
                    $savedClassType = str_ireplace("App\\Entity\\", "", $savedClassType);
                    $updatedApi = self::updateApi($doctrine, $savedClassType, $patient, $thirdPartyService, new DateTime());

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($updatedApi);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' ' . $e->getMessage());
                }

                return $dataEntry;
            }

        }

        return NULL;
    }

    /**
     * food
     *
     * @param ManagerRegistry $doctrine
     * @param String          $getContent
     *
     * @return FoodNutrition|null
     */
    public static function translateFood(ManagerRegistry $doctrine, String $getContent)
    {
        $jsonContent = self::decodeJson($getContent);
        //AppConstants::writeToLog('debug_transform.txt', __LINE__ . " -  translateFood: " . print_r($jsonContent, TRUE));

        if (property_exists($jsonContent, "uuid")) {
            ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . " - New call too FoodNutrition for " . $jsonContent->remoteId);

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

            /** @var FoodMeals $mealType */
            $mealType = self::getMealType($doctrine, self::convertMeal($jsonContent->meal_type));
            if (is_null($mealType)) {
                return NULL;
            }

//            if (property_exists($jsonContent, "title") && !empty($jsonContent->title) && self::startsWith($jsonContent->title, "MyFitnessPal")) {
//                /** @var FoodNutrition $dataEntry */
//                $dataEntry = $doctrine->getRepository(FoodNutrition::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'trackingDevice' => $deviceTracking]);
//            } else {
//                /** @var FoodNutrition $dataEntry */
//                $dataEntry = $doctrine->getRepository(FoodNutrition::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'trackingDevice' => $deviceTracking]);
//            }
            /** @var FoodNutrition $dataEntry */
            $dataEntry = $doctrine->getRepository(FoodNutrition::class)->findOneBy(['RemoteId' => $jsonContent->remoteId, 'trackingDevice' => $deviceTracking]);

            if (!$dataEntry) {
                $dataEntry = new FoodNutrition();
            }

            $dataEntry->setPatient($patient);
            $dataEntry->setRemoteId($jsonContent->remoteId);

            if (is_null($dataEntry->getDateTime()) || $dataEntry->getDateTime()->format("U") <> (new \DateTime($jsonContent->dateTime))->format("U")) {
                $dataEntry->setDateTime(new \DateTime($jsonContent->dateTime));
            }
            $dataEntry->setTrackingDevice($deviceTracking);
            $dataEntry->setMeal($mealType);

            if (property_exists($jsonContent, "carbohydrate") && $jsonContent->carbohydrate > 0) $dataEntry->setCarbohydrate($jsonContent->carbohydrate);
            if (property_exists($jsonContent, "calcium") && $jsonContent->calcium > 0) $dataEntry->setCalcium($jsonContent->calcium);
            if (property_exists($jsonContent, "calorie") && $jsonContent->calorie > 0) $dataEntry->setCalorie($jsonContent->calorie);
            if (property_exists($jsonContent, "cholesterol") && $jsonContent->cholesterol > 0) $dataEntry->setCholesterol($jsonContent->cholesterol);
            if (property_exists($jsonContent, "dietary_fiber") && $jsonContent->dietary_fiber > 0) $dataEntry->setDietaryFiber($jsonContent->dietary_fiber);
            if (property_exists($jsonContent, "iron") && $jsonContent->iron > 0) $dataEntry->setIron($jsonContent->iron);
            if (property_exists($jsonContent, "monosaturated_fat") && $jsonContent->monosaturated_fat > 0) $dataEntry->setMonosaturatedFat($jsonContent->monosaturated_fat);
            if (property_exists($jsonContent, "polysaturated_fat") && $jsonContent->polysaturated_fat > 0) $dataEntry->setPolysaturatedFat($jsonContent->polysaturated_fat);
            if (property_exists($jsonContent, "potassium") && $jsonContent->potassium > 0) $dataEntry->setPotassium($jsonContent->potassium);
            if (property_exists($jsonContent, "protein") && $jsonContent->protein > 0) $dataEntry->setProtein($jsonContent->protein);
            if (property_exists($jsonContent, "saturated_fat") && $jsonContent->saturated_fat > 0) $dataEntry->setSaturatedFat($jsonContent->saturated_fat);
            if (property_exists($jsonContent, "sodium") && $jsonContent->sodium > 0) $dataEntry->setSodium($jsonContent->sodium);
            if (property_exists($jsonContent, "sugar") && $jsonContent->sugar > 0) $dataEntry->setSugar($jsonContent->sugar);
            if (property_exists($jsonContent, "title")) $dataEntry->setTitle($jsonContent->title);
            if (property_exists($jsonContent, "total_fat") && $jsonContent->total_fat > 0) $dataEntry->setTotalFat($jsonContent->total_fat);
            if (property_exists($jsonContent, "trans_fat") && $jsonContent->trans_fat > 0) $dataEntry->setTransFat($jsonContent->trans_fat);
            if (property_exists($jsonContent, "vitamin_a") && $jsonContent->vitamin_a > 0) $dataEntry->setVitA($jsonContent->vitamin_a);
            if (property_exists($jsonContent, "vitamin_c") && $jsonContent->vitamin_c > 0) $dataEntry->setVitC($jsonContent->vitamin_c);

            if (is_null($deviceTracking->getLastSynced()) || $deviceTracking->getLastSynced()->format("U") < $dataEntry->getDateTime()->format("U")) {
                $deviceTracking->setLastSynced($dataEntry->getDateTime());
            }

            if (property_exists($jsonContent, "title") && !empty($jsonContent->title) && !self::startsWith($jsonContent->title, "MyFitnessPal")) {
                try {
                    $savedClassType = "FoodNutrition";
                    $savedClassType = str_ireplace("App\\Entity\\", "", $savedClassType);
                    $updatedApi = self::updateApi($doctrine, $savedClassType, $patient, $thirdPartyService, $dataEntry->getDateTime());

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($updatedApi);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    ///AppConstants::writeToLog('debug_transform.txt', __LINE__ . ' ' . $e->getMessage());
                }
            }

            return $dataEntry;

        }

        return NULL;
    }
}