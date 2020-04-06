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
/** @noinspection DuplicatedCode */

namespace App\AwardDelivery;


/**
 * Class AwardDelivery
 *
 * @package App\AwardDelivery
 */
class AwardDelivery
{
    /**
     * @param string $reasoning
     *
     * @return string
     */
    protected function replaceTags(string $reasoning)
    {
        if (preg_match('/{DATE:(.+)}/im', $reasoning, $regs)) {
            $reasoning = str_ireplace("{DATE:" . $regs[1] . "}", date($regs[1]), $reasoning);
        }
        $reasoning = str_ireplace("{DATE}", date("Y-m-d"), $reasoning);

        return $reasoning;
    }
}
