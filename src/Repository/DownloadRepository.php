<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Download;
use App\Entity\Package;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Download|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Download[] findAll()
 * @method Download|null findOneBy(array $criteria, array $orderBy = null)
 * @method Download[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class DownloadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Download::class);
    }

    /**
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
