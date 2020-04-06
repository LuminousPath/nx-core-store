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

namespace App;


use App\Entity\Patient;
use App\Entity\ThirdPartyService;
use DateTimeInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AppConstants
 *
 * @package App
 */
class AppConstants
{
    /**
     * @param ManagerRegistry   $doctrine
     * @param Patient           $patient
     * @param DateTimeInterface $dateTime
     * @param string            $name
     * @param float             $xp
     * @param string            $image
     * @param string            $text
     * @param string            $longtext
     *
     * @return Patient
     */
    static function awardPatientReward(ManagerRegistry $doctrine, Patient $patient, DateTimeInterface $dateTime, string $name, float $xp, string $image, string $text, string $longtext)
    {
        return $patient;
    }

    /**
     * @param String $fileName
     * @param String $body
     */
    static function writeToLog(String $fileName, String $body)
    {
        try {
            $path = sys_get_temp_dir() . '/sync_upload_post';
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

        if (!empty($path)) {
            $file = $path . '/' . $fileName;

            $fileSystem = new Filesystem();
            try {
                $fileSystem->mkdir($path);
                if ($fileSystem->exists($file)) {
                    $fileSystem->appendToFile($file, date("Y-m-d H:i:s") . ":: " . $body . "\n");
                } else {
                    $fileSystem->dumpFile($file, date("Y-m-d H:i:s") . ":: " . $body . "\n");
                }

            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at " . $exception->getPath();
            }
        }
    }

    /**
     * @param ManagerRegistry   $doctrine
     * @param Patient           $patient
     * @param float             $xpAwarded
     * @param string            $reasoning
     * @param DateTimeInterface $dateTime
     *
     * @return Patient
     */
    static function awardPatientXP(ManagerRegistry $doctrine, Patient $patient, float $xpAwarded, string $reasoning, DateTimeInterface $dateTime)
    {
        return $patient;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    static function startsWith($haystack, $needle)
    {
        $len = strlen($needle);
        return (substr($haystack, 0, $len) === $needle);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param String          $serviceName
     *
     * @return ThirdPartyService|null
     */
    static function getThirdPartyService(ManagerRegistry $doctrine, String $serviceName)
    {
        /** @var ThirdPartyService $thirdPartyService */
        $thirdPartyService = $doctrine->getRepository(ThirdPartyService::class)->findOneBy(['name' => $serviceName]);
        if ($thirdPartyService) {
            return $thirdPartyService;
        } else {
            $entityManager = $doctrine->getManager();
            $thirdPartyService = new ThirdPartyService();
            $thirdPartyService->setName($serviceName);
            $entityManager->persist($thirdPartyService);
            $entityManager->flush();

            return $thirdPartyService;
        }
    }

    /**
     * @param \Twig\Environment $twig
     * @param Swift_Mailer      $mailer
     * @param array             $setTo
     * @param string            $setTemplateName
     * @param array             $setTemplateVariables
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    static function sendUserEmail(\Twig\Environment $twig, Swift_Mailer $mailer, array $setTo, string $setTemplateName, array $setTemplateVariables)
    {
        // Create the message
        $message = (new Swift_Message())
            // Add subject
            ->setSubject($setTemplateVariables['html_title'])
            //Put the From address
            ->setFrom([$_ENV['SITE_EMAIL_NOREPLY'] => $_ENV['SITE_EMAIL_NAME']])
            ->setBody(
                $twig->render(
                    'emails/' . $setTemplateName . '.html.twig',
                    $setTemplateVariables
                ),
                'text/html'
            )
            // you can remove the following code if you don't define a text version for your emails
            ->addPart(
                $twig->render(
                    'emails/' . $setTemplateName . '.txt.twig',
                    $setTemplateVariables
                ),
                'text/plain'
            );

        if (count($setTo) > 1) {
            // Include several To addresses
            $message->setTo([$_ENV['SITE_EMAIL_NOREPLY'] => $_ENV['SITE_EMAIL_NAME']]);
            // Include several To addresses
            $message->setBcc($setTo);
        } else {
            // Include several To addresses
            $message->setTo($setTo);
        }

        $mailer->send($message);
    }

    /**
     * @param $criteria
     *
     * @return string
     */
    static function convertCriteriaEnglish($criteria)
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

    /**
     * @param $stringToCompress
     *
     * @return string
     */
    static function compressString($stringToCompress)
    {
        $compressedString = "\x1f\x8b\x08\x00" . gzcompress($stringToCompress);

        return $compressedString;
    }

    /**
     * @param $stringToUncompress
     *
     * @return false|string
     */
    static function uncompressString($stringToUncompress)
    {
        $uncompressedString = gzuncompress(substr($stringToUncompress, 4));

        return $uncompressedString;
    }

    /**
     * @param      $seconds
     * @param bool $withHours
     *
     * @return string
     */
    static function formatSeconds($seconds, bool $withHours = TRUE)
    {
        $hours = 0;
        $milliseconds = str_replace("0.", '', $seconds - floor($seconds));

        if ($seconds > 3600) {
            $hours = floor($seconds / 3600);
        }
        $seconds = $seconds % 3600;

        if ($withHours) {
            return str_pad($hours, 2, '0', STR_PAD_LEFT)
                . gmdate(':i:s', $seconds)
                . ($milliseconds ? ".$milliseconds" : '');
        } else {
            return gmdate('i:s', $seconds)
                . ($milliseconds ? ".$milliseconds" : '');
        }
    }

    /**
     * @param $value
     * @param $valueUnit
     * @param $targetUnit
     *
     * @return float|int
     */
    static function convertUnitOfMeasurement($value, $valueUnit, $targetUnit)
    {
        if ($valueUnit == "mile" && $targetUnit == "meter") {
            return $value * 1609.34;
        } else if ($valueUnit == "meter" && $targetUnit == "mile") {
            return $value / 1609.34;
        } else if ($valueUnit == "meter" && $targetUnit == "km") {
            return $value / 1000;
        }

        return 0.5;
    }
}
