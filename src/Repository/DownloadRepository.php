<?php

namespace App\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\VersionInterface;

/**
 * Class DownloadRepository
 *
 * @package App\Devliver\Repository
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class DownloadRepository extends EntityRepository
{

    /**
     * @param PackageInterface $package
     *
     * @return mixed
     */
    public function countPackageDownloads(PackageInterface $package)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(p.id)');

        $qb->where($qb->expr()->in('p.package', ':package'));
        $qb->setParameter('package', $package->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param VersionInterface $version
     *
     * @return mixed
     */
    public function countVersionDownloads(VersionInterface $version)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(p.id)');

        $qb->where($qb->expr()->in('p.version', ':version'));
        $qb->setParameter('version', $version->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }
}
