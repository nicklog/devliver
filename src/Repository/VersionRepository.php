<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Version|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Version[] findAll()
 * @method Version|null findOneBy(array $criteria, array $orderBy = null)
 * @method Version[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
final class VersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Version::class);
    }

    /**
     * @return Version[]
     */
    public function findByPackage(?Package $package = null): array
    {
        $qb   = $this->createQueryBuilder('v');
        $expr = $qb->expr();

        $qb->join('v.package', 'p');
        $qb->andWhere($expr->eq('p.enable', true));

        if ($package !== null) {
            $qb->andWhere($expr->in('p.id', ':package'));
            $qb->setParameter('package', $package->getId());
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
