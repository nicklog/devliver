<?php

namespace Shapecode\Devliver\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shapecode\Devliver\Entity\Package;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class PackageListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class PackageListener implements EventSubscriber
{

    /** @var TagAwareAdapterInterface */
    protected $cache;

    /**
     * @param TagAwareAdapterInterface $cache
     */
    public function __construct(TagAwareAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof Package) {
            $this->cache->invalidateTags(['packages.json']);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof Package) {
            $this->deleteTags($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof Package) {
            $this->deleteTags($entity);
        }
    }

    /**
     * @param Package $package
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function deleteTags(Package $package)
    {
        $this->cache->clear();
//        $this->cache->invalidateTags(['packages-package-' . $package->getId()]);
    }

}
