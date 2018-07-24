<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\Author;

/**
 * Class AuthorRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class AuthorRepository extends EntityRepository
{

    /**
     * @param string|null $name
     * @param string|null $email
     * @param bool        $create
     *
     * @return null|Author
     */
    public function findByNameOrEmail(?string $name, ?string $email, bool $create = false): ?Author
    {
        $qb = $this->createQueryBuilder('p');
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
