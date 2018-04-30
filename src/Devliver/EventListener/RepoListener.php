<?php

namespace Shapecode\Devliver\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shapecode\Devliver\Entity\RepoInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class RepoListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class RepoListener implements EventSubscriber
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
            Events::postPersist => 'onPostPersist',
            Events::postUpdate  => 'onPostUpdate',
            Events::preRemove   => 'onPreRemove',
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onPostPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof RepoInterface) {
            $this->cache->invalidateTags(['packages.json']);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function onPostUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof RepoInterface) {
            $this->deleteTags($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function onPreRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof RepoInterface) {
            $this->deleteTags($entity);
        }
    }

    /**
     * @param RepoInterface $repo
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function deleteTags(RepoInterface $repo)
    {
        $this->cache->invalidateTags(['packages-repo-' . $repo->getId()]);
    }

}
