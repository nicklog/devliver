<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;

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

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->setApiToken($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->setApiToken($args);
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $done = $this->setApiToken($args);

        if (! $done) {
            return;
        }

        $args->getObjectManager()->persist($args->getEntity());
        $args->getObjectManager()->flush();
    }

    public function setApiToken(LifecycleEventArgs $args): bool
    {
        $user = $args->getEntity();
        if (! ($user instanceof User)) {
            return false;
        }

        if ($user->getApiToken() === null) {
            $token = Uuid::uuid4()->toString();

            $user->setApiToken($token);
        }

        if ($user->getRepositoryToken() === null) {
            $token = Uuid::uuid4()->toString();

            $user->setRepositoryToken($token);
        }

        return true;
    }
}
