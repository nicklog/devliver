<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Common\AbstractEntity;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class EntityListener implements EventSubscriber
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
        $entity = $args->getEntity();

        $this->updateUpdatedAt($entity);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->updateUpdatedAt($entity);
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->updateUpdatedAt($entity);

        $args->getEntityManager()->persist($entity);
    }

    /**
     * @param mixed $entity
     */
    protected function updateUpdatedAt($entity): void
    {
        if (! ($entity instanceof AbstractEntity)) {
            return;
        }

        $entity->setUpdated(new DateTime());
    }
}
