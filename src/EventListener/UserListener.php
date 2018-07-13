<?php

namespace Shapecode\Devliver\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shapecode\Devliver\Entity\User;
use Tenolo\Utilities\Utils\CryptUtil;

/**
 * Class UserListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class UserListener implements EventSubscriber
{

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->setApiToken($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->setApiToken($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $done = $this->setApiToken($args);

        if ($done) {
            $args->getObjectManager()->persist($args->getEntity());
            $args->getObjectManager()->flush();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @return bool
     */
    public function setApiToken(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();
        if (!($user instanceof User)) {
            return false;
        }

        if ($user->getApiToken() === null) {
            $token = CryptUtil::getRandomHash();

            $user->setApiToken($token);
        }

        if ($user->getRepositoryToken() === null) {
            $token = CryptUtil::getRandomHash();

            $user->setRepositoryToken($token);
        }

        return true;
    }
}
