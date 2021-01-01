<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Package;
use App\Entity\Version;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class PackageListener implements EventSubscriber
{
    private TagAwareAdapterInterface $cache;

    public function __construct(TagAwareAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
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

        if (
            ! ($entity instanceof Package) &&
            ! ($entity instanceof Version)
        ) {
            return;
        }

        $this->cache->clear();
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (
            ! ($entity instanceof Package) &&
            ! ($entity instanceof Version)
        ) {
            return;
        }

        $this->cache->clear();
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (
            ! ($entity instanceof Package) &&
            ! ($entity instanceof Version)
        ) {
            return;
        }

        $this->cache->clear();
    }
}
