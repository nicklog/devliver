<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Entity\Version;

/**
 * Class VersionRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class VersionRepository extends EntityRepository
{

    /**
     * @param User                  $user
     * @param Package|null $package
     *
     * @return array|Version[]
     */
    public function findAccessibleForUser(User $user, Package $package = null): array
    {
        $qb = $this->createQueryBuilder('v');
        $expr = $qb->expr();

        $qb->join('v.package', 'p');
        $qb->andWhere($expr->eq('p.enable', true));

        if ($package) {
            $qb->andWhere($expr->in('p.id', ':package'));
            $qb->setParameter('package', $package->getId());
        }

        if (!$user->isPackageRootAccess()) {
            $versions = $user->getAccessVersions();
            $versionIds = $versions->map(function (Version $version) {
                return $version->getId();
            })->toArray();

            $qb->andWhere($expr->in('v.id', ':version_ids'));
            $qb->setParameter('version_ids', $versionIds);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

}
