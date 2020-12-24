<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use App\Entity\UpdateQueue;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UpdateQueue|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method UpdateQueue[] findAll()
 * @method UpdateQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpdateQueue[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class UpdateQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpdateQueue::class);
    }

    /**
     * @return object|UpdateQueue|null
     */
    public function findOneByPackage(Package $package): ?UpdateQueue
    {
        return $this->findOneBy([
            'package' => $package->getId(),
        ]);
    }

    /**
     * @return array|UpdateQueue[]
     */
    public function findUnlocked(): array
    {
        $qb   = $this->createQueryBuilder('p');
        $expr = $qb->expr();

        $qb->where($expr->isNull('p.lockedAt'));
        $qb->orWhere($expr->lte('p.lockedAt', ':pastLockedAt'));

        $past = new DateTime('-10 minutes', new DateTimeZone('UTC'));
        $qb->setParameter('pastLockedAt', $past);

        return $qb->getQuery()->getResult();
    }
}
