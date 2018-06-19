<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\Tag;

/**
 * Class TagRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class TagRepository extends EntityRepository
{

    /**
     * @param string $name
     * @param bool   $create
     *
     * @return Tag|null
     */
    public function findByName(string $name, bool $create = false): ?Tag
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.name = :name');
        $qb->setMaxResults(1);
        $qb->setParameter('name', $name);

        $tag = $qb->getQuery()->getOneOrNullResult();

        if ($tag === null && $create) {
            $className = $this->getClassName();

            $tag = new $className();
            $tag->setName($name);

            $this->getEntityManager()->persist($tag);
            $this->getEntityManager()->flush($tag);
        }

        return $tag;
    }
}
