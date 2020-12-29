<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use Composer\Package\PackageInterface as ComposerPackageInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Package|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Package[] findAll()
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
final class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    /**
     * @return Package[]
     */
    public function findEnabled(): array
    {
        $qb   = $this->createQueryBuilder('p');
        $expr = $qb->expr();

        $qb->andWhere($expr->eq('p.enable', true));

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findOneByComposerPackage(ComposerPackageInterface $package): ?Package
    {
        return $this->findOneBy([
            'name' => $this->findOneByName($package->getName()),
        ]);
    }

    /**
     * @return Package[]
     */
    public function findWithRepos(): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where($qb->expr()->isNotNull('p.repo'));

        return $qb->getQuery()->getResult();
    }

    public function findOneByName(string $name): ?Package
    {
        return $this->findOneBy([
            'name' => $name,
        ]);
    }
}
