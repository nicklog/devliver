<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Author|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Author[] findAll()
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function findByNameOrEmail(
        ?string $name,
        ?string $email,
        bool $create = false
    ): ?Author {
        $qb   = $this->createQueryBuilder('p');
        $expr = $qb->expr();

        if ($email !== null) {
            $qb->andWhere($expr->eq('p.email', ':email'));
            $qb->setParameter('email', $email);
        } else {
            $qb->andWhere($expr->isNull('p.email'));
        }

        if ($name !== null) {
            $qb->andWhere($expr->eq('p.name', ':name'));
            $qb->setParameter('name', $name);
        } else {
            $qb->andWhere($expr->isNull('p.name'));
        }

        $qb->setMaxResults(1);

        $author = $qb->getQuery()->getOneOrNullResult();

        if ($author === null && $create) {
            $className = $this->getClassName();

            $author = new $className();
            $author->setName($name);
            $author->setEmail($email);

            $this->getEntityManager()->persist($author);
            $this->getEntityManager()->flush($author);
        }

        return $author;
    }
}
