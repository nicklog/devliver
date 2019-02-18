<?php

namespace Shapecode\Devliver\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shapecode\Devliver\Entity\BaseEntity;

/**
 * Class EntityListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
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

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->updateCreatedAt($entity);
        $this->updateUpdatedAt($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->updateCreatedAt($entity);
        $this->updateUpdatedAt($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->updateCreatedAt($entity);
        $this->updateUpdatedAt($entity);

        $args->getEntityManager()->persist($entity);
    }

    /**
     * @param $entity
     *
     * @throws \Exception
     */
    protected function updateCreatedAt($entity)
    {
        if (!($entity instanceof BaseEntity)) {
            return;
        }

        $year = (int)$entity->getCreatedAt()->format('Y');
        if ($entity->getCreatedAt() !== null && $year > 0) {
            return;
        }

        $entity->setCreatedAt(new \DateTime());
    }

    /**
     * @param $entity
     *
     * @throws \Exception
     */
    protected function updateUpdatedAt($entity)
    {
        if (!($entity instanceof BaseEntity)) {
            return;
        }

        $entity->setUpdatedAt(new \DateTime());
    }

}
