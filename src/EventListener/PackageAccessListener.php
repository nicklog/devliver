<?php

namespace Shapecode\Devliver\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Entity\Version;
use Shapecode\Devliver\Repository\UserRepository;

/**
 * Class PackageAccessListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class PackageAccessListener implements EventSubscriber
{

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Package) {
            $this->addUsersToPackage($args);
        }

        if ($entity instanceof Version) {
            $this->addUsersToVersion($args);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function addUsersToPackage(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        /** @var Package $package */
        $package = $args->getEntity();

        /** @var UserRepository $userRepo */
        $userRepo = $em->getRepository(User::class);

        /** @var User[] $users */
        $users = $userRepo->findBy([
            'autoAddToNewPackages' => true
        ]);

        foreach ($users as $user) {
            $user->addAccessPackage($package);
            $em->persist($user);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function addUsersToVersion(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        /** @var Version $version */
        $version = $args->getEntity();
        $package = $version->getPackage();

        /** @var UserRepository $userRepo */
        $userRepo = $em->getRepository(User::class);

        /** @var User[] $users */
        $users = $userRepo->findBy([
            'autoAddToNewVersions' => true
        ]);

        foreach ($users as $user) {
            if ($user->hasAccessPackage($package)) {
                $user->addAccessVersion($version);
                $em->persist($user);
            }
        }
    }
}
