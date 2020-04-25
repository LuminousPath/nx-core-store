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

namespace App\EventSubscriber\Doctrine;


use App\AppConstants;
use App\Service\ServerToServer;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class ShareEntitiesWithMembers implements EventSubscriber
{
    /** @var ServerToServer $serverComms */
    private $serverComms;

    public function __construct(ServerToServer $serverComms)
    {
        $this->serverComms = $serverComms;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /** @noinspection DuplicatedCode */
    public function postPersist(LifecycleEventArgs $args)
    {
        if (!array_key_exists("EntityEnv", $_ENV)) {
            $entity = $args->getObject();
            $this->serverComms->sentToMembers($entity, "persist");
        } else {
            if ($_ENV['EntityEnv'] == "ServerComms" || $_ENV['EntityEnv'] == "Migrate") {
                AppConstants::writeToLog('debug_transform.txt',
                    "EntityEnv: " . $_ENV['EntityEnv'] . " ServerComms blocked");
            } else {
                $entity = $args->getObject();
                $this->serverComms->sentToMembers($entity, "persist");
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if (!array_key_exists("EntityEnv", $_ENV)) {
            $entity = $args->getObject();
            $this->serverComms->sentToMembers($entity, "update");
        } else {
            if ($_ENV['EntityEnv'] == "ServerComms" || $_ENV['EntityEnv'] == "Migrate") {
                AppConstants::writeToLog('debug_transform.txt',
                    "EntityEnv: " . $_ENV['EntityEnv'] . " ServerComms blocked");
            } else {
                $entity = $args->getObject();
                $this->serverComms->sentToMembers($entity, "update");
            }
        }
    }

    /** @noinspection DuplicatedCode */
    public function postRemove(LifecycleEventArgs $args)
    {
        if (!array_key_exists("EntityEnv", $_ENV)) {
            $entity = $args->getObject();
            $this->serverComms->sentToMembers($entity, "remove");
        } else {
            if ($_ENV['EntityEnv'] == "ServerComms" || $_ENV['EntityEnv'] == "Migrate") {
                AppConstants::writeToLog('debug_transform.txt',
                    "EntityEnv: " . $_ENV['EntityEnv'] . " ServerComms blocked");
            } else {
                $entity = $args->getObject();
                $this->serverComms->sentToMembers($entity, "remove");
            }
        }
    }
}
