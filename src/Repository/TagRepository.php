<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Tag[] findAll()
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

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
