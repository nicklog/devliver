<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\Version;

/**
 * Class DownloadRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class DownloadRepository extends EntityRepository
{

    /**
     * @param Package $package
     *
     * @return mixed
     */
    public function countPackageDownloads(Package $package)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(p.id)');

        $qb->where($qb->expr()->in('p.package', ':package'));
        $qb->setParameter('package', $package->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Version $version
     *
     * @return mixed
     */
    public function countVersionDownloads(Version $version)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(p.id)');

        $qb->where($qb->expr()->in('p.version', ':version'));
        $qb->setParameter('version', $version->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }
}
