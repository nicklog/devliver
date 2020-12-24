<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use App\Entity\User;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Version|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Version[] findAll()
 * @method Version|null findOneBy(array $criteria, array $orderBy = null)
 * @method Version[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class VersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Version::class);
    }

    /**
     * @return array|Version[]
     */
    public function findAccessibleForUser(User $user, ?Package $package = null): array
    {
        $qb   = $this->createQueryBuilder('v');
        $expr = $qb->expr();

        $qb->join('v.package', 'p');
        $qb->andWhere($expr->eq('p.enable', true));

        if ($package) {
            $qb->andWhere($expr->in('p.id', ':package'));
            $qb->setParameter('package', $package->getId());
        }

        if (! $user->isPackageRootAccess()) {
            $versions   = $user->getAccessVersions();
            $versionIds = $versions->map(static function (Version $version) {
                return $version->getId();
            })->toArray();

            $qb->andWhere($expr->in('v.id', ':version_ids'));
            $qb->setParameter('version_ids', $versionIds);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
