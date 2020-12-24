<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Package;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class PackageListener implements EventSubscriber
{
    protected TagAwareAdapterInterface $cache;

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

    public function postPersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (! ($entity instanceof Package)) {
            return;
        }

        $this->cache->invalidateTags(['packages.json']);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (! ($entity instanceof Package)) {
            return;
        }

        $this->deleteTags($entity);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (! ($entity instanceof Package)) {
            return;
        }

        $this->deleteTags($entity);
    }

    protected function deleteTags(Package $package): void
    {
        $this->cache->clear();
    }
}
