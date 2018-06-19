<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\UpdateQueue;

/**
 * Class UpdateQueueRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class UpdateQueueRepository extends EntityRepository
{

    /**
     * @param PackageInterface $package
     *
     * @return null|object|UpdateQueue
     */
    public function findOneByPackage(PackageInterface $package): ?UpdateQueue
    {
        return $this->findOneBy([
            'package' => $package->getId()
        ]);
    }

    /**
     * @return array|UpdateQueue[]
     */
    public function findUnlocked(): array
    {
        $qb = $this->createQueryBuilder('p');
        $expr = $qb->expr();

        $qb->where($expr->isNull('p.lockedAt'));
        $qb->orWhere($expr->lte('p.lockedAt', ':pastLockedAt'));

        $past = new \DateTime('-10 minutes', new \DateTimeZone('UTC'));
        $qb->setParameter('pastLockedAt', $past);

        return $qb->getQuery()->getResult();
    }
}
