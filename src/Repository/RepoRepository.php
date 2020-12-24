<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Repo|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Repo[] findAll()
 * @method Repo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repo[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class RepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repo::class);
    }
}
