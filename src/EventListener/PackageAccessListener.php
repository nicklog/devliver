<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Package;
use App\Entity\User;
use App\Entity\Version;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use function assert;

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

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Package) {
            $this->addUsersToPackage($args);
        }

        if (! ($entity instanceof Version)) {
            return;
        }

        $this->addUsersToVersion($args);
    }

    protected function addUsersToPackage(LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $package = $args->getEntity();
        assert($package instanceof Package);

        $userRepo = $em->getRepository(User::class);
        assert($userRepo instanceof UserRepository);

        /** @var User[] $users */
        $users = $userRepo->findBy([
            'autoAddToNewPackages' => true,
        ]);

        foreach ($users as $user) {
            $user->addAccessPackage($package);
            $em->persist($user);
        }
    }

    protected function addUsersToVersion(LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $version = $args->getEntity();
        assert($version instanceof Version);
        $package = $version->getPackage();

        $userRepo = $em->getRepository(User::class);
        assert($userRepo instanceof UserRepository);

        /** @var User[] $users */
        $users = $userRepo->findBy([
            'autoAddToNewVersions' => true,
        ]);

        foreach ($users as $user) {
            if (! $user->hasAccessPackage($package)) {
                continue;
            }

            $user->addAccessVersion($version);
            $em->persist($user);
        }
    }
}
