<?php

/*
* This file is part of the Storage module in NxFIFTEEN Core.
*
* Copyright (c) 2019. Stuart McCulloch Anderson
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @package     Store
* @version     0.0.0.x
* @since       0.0.0.1
* @author      Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
* @link        https://nxfifteen.me.uk NxFIFTEEN
* @link        https://git.nxfifteen.rocks/nx-health NxFIFTEEN Core
* @link        https://git.nxfifteen.rocks/nx-health/store NxFIFTEEN Core Storage
* @copyright   2019 Stuart McCulloch Anderson
* @license     https://license.nxfifteen.rocks/mit/2015-2019/ MIT
*/

namespace App\Controller;

use App\Entity\ApiAccessLog;
use App\Entity\Patient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LastApiAccessController extends AbstractController
{

    /**
     * @Route("/json/{id}/api/{endpoint}/{service}/last", name="get_endpoint_last_pulled")
     * @param String $id A users UUID
     * @param String $service The Service ID requested
     * @param String $endpoint The endpoint name
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index(String $id, String $service, String $endpoint)
    {
        $return = [];

        /** @var Patient $patient */
        $patient = $this->getDoctrine()
            ->getRepository(Patient::class)
            ->findByUuid($id);

        if (!$patient) {
            $return['status'] = "error";
            $return['code'] = "404";
            $return['message'] = "Patient not found with UUID specified";
            $return['payload'] = "2000-01-01 00:00:00.000";

            return $this->json($return);
        }

        /** @var ApiAccessLog $patient */
        $apiAccessLog = $this->getDoctrine()
            ->getRepository(ApiAccessLog::class)
            ->findLastAccess($patient, $service, $endpoint);

        if (!$apiAccessLog) {
            $return['status'] = "warning";
            $return['code'] = "201";
            $return['message'] = "No last access log for Service/EndPoint combination";
            $return['payload'] = "2000-01-01 00:00:00.000";

            return $this->json($return);
        }

        $return['status'] = "okay";
        $return['code'] = "200";
        $return['message'] = "";
        $return['payload'] = $apiAccessLog->getLastRetrieved()->format("Y-m-d H:i:s.v");

        return $this->json($return);
    }
}
