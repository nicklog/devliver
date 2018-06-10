<?php

namespace Shapecode\Devliver\Repository;

use Composer\Package\PackageInterface as ComposerPackageInterface;
use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\User;

/**
 * Class PackageRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class PackageRepository extends EntityRepository
{

    /**
     * @param User $user
     *
     * @return array|PackageInterface[]
     */
    public function findAccessibleForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('p');
        $expr = $qb->expr();

        if (!$user->isPackageRootAccess()) {
            $packages = $user->getAccessPackages();
            $packageIds = $packages->map(function (PackageInterface $package) {
                return $package->getId();
            })->toArray();

            $qb->andWhere($expr->in('p.id', ':package_ids'));
            $qb->setParameter('package_ids', $packageIds);
        }

        $qb->andWhere($expr->eq('p.enable', true));

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param ComposerPackageInterface $package
     *
     * @return null|object|PackageInterface
     */
    public function findOneByComposerPackage(ComposerPackageInterface $package)
    {
        return $this->findOneBy([
            'name' => $this->findOneByName($package->getName())
        ]);
    }

    /**
     * @return PackageInterface[]
     */
    public function findWithRepos()
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where($qb->expr()->isNotNull('p.repo'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $name
     *
     * @return null|object|PackageInterface
     */
    public function findOneByName($name)
    {
        return $this->findOneBy([
            'name' => $name
        ]);
    }
}
