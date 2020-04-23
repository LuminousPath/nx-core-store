<?php
/**
 * This file is part of NxFIFTEEN Fitness Core.
 *
 * @link      https://nxfifteen.me.uk/projects/nxcore/
 * @link      https://gitlab.com/nx-core/store
 * @author    Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @copyright Copyright (c) 2020. Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @license   https://nxfifteen.me.uk/api/license/mit/license.html MIT
 */

/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Service;


use App\AppConstants;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ServerToServer
{
    /** @var KernelInterface $appKernel */
    private $appKernel;

    /** @var PkiManager $pkiManager */
    private $pkiManager;

    /** @var array $membershipServers */
    private $membershipServers;

    /** @var string $protical */
    private $protical = "0.0.1";

    /** @var array $membershipServer */
    private $membershipServer;

    /** @var int $databaseVersion */
    private $databaseVersion;

    /** @var Connection $connection */
    private $connection;

    /**
     * ServerToServer constructor.
     *
     * @param PkiManager      $pkiManager
     * @param KernelInterface $appKernel
     * @param Connection      $connection
     */
    public function __construct(PkiManager $pkiManager, KernelInterface $appKernel, Connection $connection)
    {
        $this->pkiManager = $pkiManager;
        $this->appKernel = $appKernel;
        $this->connection = $connection;
        $this->databaseVersion = $this->getDatabaseVersion('/var/www/localhost/core/store/src/Migrations');

        $membershipFile = $appKernel->getProjectDir() . '/var/private/membership.yml';
        $this->membershipServers = Yaml::parseFile($membershipFile);
    }

    /**
     * @param $origin
     *
     * @return bool|array
     */
    private function findMember($origin)
    {
        foreach ($this->membershipServers as $membershipServer) {
            if ($membershipServer['host'] == $origin) {
                $this->membershipServer = $membershipServer;
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $migrationsDir
     *
     * @return string
     */
    private function getDatabaseVersion(string $migrationsDir)
    {
        $configuration = new Configuration($this->connection);
        $configuration->setName('Application Migrations');
        $configuration->setMigrationsDirectory($migrationsDir);
        $configuration->setMigrationsNamespace("DoctrineMigrations"); // "DoctrineMigrations"
        $configuration->setMigrationsTableName('migration_versions');

        $migrations = $configuration->getMigratedVersions();
        return intval($migrations[count($migrations) - 1]);
    }

    /**
     * @param string $get_class
     * @param array  $membershipServer
     *
     * @return bool
     */
    private function isAllowedEntity(string $get_class, array $membershipServer)
    {
        if (array_key_exists("allowed", $membershipServer)) {
            if (is_string($membershipServer['allowed']) && $membershipServer['allowed'] == "all") {
                return true;
            } else {
                if (is_array($membershipServer['allowed']) && in_array($get_class, $membershipServer['allowed'])) {
                    return true;
                }
            }
        } else {
            //@Todo REMOVE THIS
            return true;
        }

        return false;
    }

    private function isPrivateEntity(string $get_class)
    {
        $privateEntities = [
            //"App\Entity\Patient",
        ];
        return in_array($get_class, $privateEntities);
    }

    private function isProticalNew($protical)
    {
        $testLocalProtical = str_ireplace(".", "", $this->protical);
        $testRemoteProtical = str_ireplace(".", "", $protical);
        if ($testLocalProtical < $testRemoteProtical) {
            return false;
        }

        return true;
    }

    private function isProticalOld($protical)
    {
        $testLocalProtical = str_ireplace(".", "", $this->protical);
        $testRemoteProtical = str_ireplace(".", "", $protical);
        if ($testLocalProtical > $testRemoteProtical) {
            return false;
        }

        return true;
    }

    private function isSigValid($recivedData)
    {
        $dataThatWasHashed = hash("sha256",
            $recivedData['protical'] . $recivedData['origin'] . $recivedData['data'] . $recivedData['timestamp']);
        return $this->pkiManager->isSigVerifiable($dataThatWasHashed, $recivedData['signature'],
            $this->membershipServer['public_key']);
    }

    /**
     * @return int
     */
    private function memberCount()
    {
        return count($this->membershipServers);
    }

    /**
     * @param string $body
     * @param string $dest
     */
    private function postToMirror(string $body, string $dest)
    {
        $ch = curl_init($dest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);

        $info = curl_getinfo($ch);
        switch ($info['http_code']) {
            case JsonResponse::HTTP_FORBIDDEN;
                $info['http_code'] = "Forbidden";
                break;
            case JsonResponse::HTTP_NO_CONTENT;
                $info['http_code'] = "Okay";
                break;
            case JsonResponse::HTTP_NOT_ACCEPTABLE;
                $info['http_code'] = "Not Acceptable";
                break;
            case JsonResponse::HTTP_NOT_IMPLEMENTED;
                $info['http_code'] = "Not Implemented";
                break;
            case JsonResponse::HTTP_PRECONDITION_FAILED;
                $info['http_code'] = "Precondition Failed";
                break;
        }
//        AppConstants::writeToLog('debug_transform.txt',
//            'Responded in ' . $info['total_time'] . ' seconds with code ' . $info['http_code']);

        curl_close($ch);
    }

    /**
     * @return JsonResponse
     */
    private function returnBadSignature()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NOT_ACCEPTABLE);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnNotAMember()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnOkay()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NO_CONTENT);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnSentFromDatabaseNew()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NOT_IMPLEMENTED);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnSentFromDatabaseOld()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_PRECONDITION_FAILED);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnSentFromProticalNew()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NOT_IMPLEMENTED);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function returnSentFromProticalOld()
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_PRECONDITION_FAILED);
        return $response;
    }

    /**
     * @param string $recivedData
     *
     * @return int|mixed
     */
    public function recievedFromMember(string $recivedData)
    {
        if ($this->memberCount() == 0) {
            return $this->returnNotAMember();
        }

        $recivedData = json_decode($recivedData, true);

        if (!$this->findMember($recivedData['origin'])) {
            return $this->returnNotAMember();
        }

        AppConstants::writeToLog('debug_transform.txt',
            'Incomming from ' . $this->membershipServer['host'] . '(' . $this->membershipServer['contact'] . ')');

        if (!$this->isProticalOld($recivedData['protical'])) {
            return $this->returnSentFromProticalOld();
        }

        if (!$this->isProticalNew($recivedData['protical'])) {
            return $this->returnSentFromProticalNew();
        }

        if (!$this->isSigValid($recivedData)) {
            return $this->returnBadSignature();
        }

        $decryptedData = $this->pkiManager->decryptData($recivedData['data'], $recivedData['keys']);

        $decryptedData = json_decode($decryptedData, true);
        $decryptedData['data'] = json_decode($decryptedData['data'], true);

        if ($decryptedData['version'] < $this->databaseVersion) {
            return $this->returnSentFromDatabaseOld();
        } else {
            if ($decryptedData['version'] > $this->databaseVersion) {
                return $this->returnSentFromDatabaseNew();
            }
        }

        AppConstants::writeToLog('debug_transform.txt', 'Decypted Data = ' . print_r($decryptedData, true));
        return $this->returnOkay();
    }

    /**
     * @param $dataEntity
     *
     * @return bool
     */
    public function sentToMembers($dataEntity)
    {
        if ($this->isPrivateEntity(get_class($dataEntity))) {
            AppConstants::writeToLog('debug_transform.txt', '' . __LINE__);
            return false;
        }

        $dataObject = [
            "className" => get_class($dataEntity),
            "data" => $dataEntity,
        ];

        if (is_object($dataObject['data'])) {
            $dataObject['data'] = AppConstants::toJson($dataObject['data']);
        }

        $dataObject['version'] = $this->databaseVersion;

        $dataObject = json_encode($dataObject);

        foreach ($this->membershipServers as $membershipServer) {
            if ($this->isAllowedEntity(get_class($dataEntity), $membershipServer)) {
                AppConstants::writeToLog('debug_transform.txt',
                    'Sending ' . get_class($dataEntity) . ' to ' . $membershipServer['host']);

                $messagePacket = [
                    "protical" => $this->protical,
                    "origin" => $_ENV['INSTALL_URL'],
                    "timestamp" => date("U"),
                ];

                [$messagePacket['data'], $messagePacket['keys']] = $this->pkiManager->encryptData($dataObject,
                    $membershipServer['public_key']);

                $dataToHash = hash("sha256",
                    $messagePacket['protical'] . $messagePacket['origin'] . $messagePacket['data'] . $messagePacket['timestamp']);
                $messagePacket['signature'] = $this->pkiManager->signData($dataToHash);

                $signed = $this->pkiManager->isSigVerifiable($dataToHash, $messagePacket['signature'],
                    $this->pkiManager->getPublicKey());
                if ($signed) {
                    $this->postToMirror(json_encode($messagePacket), $membershipServer['host'] . 'sync/membership/');
                } else {
                    AppConstants::writeToLog('debug_transform.txt', 'Some when wrong checking the signature');
                }
            }
        }

        return true;
    }

}
