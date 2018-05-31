<?php

namespace Shapecode\Devliver\Repository;

use Composer\Package\PackageInterface as ComposerPackageInterface;
use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\PackageInterface;

/**
 * Class PackageRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class PackageRepository extends EntityRepository
{

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
