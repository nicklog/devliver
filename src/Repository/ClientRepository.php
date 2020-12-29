<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method Client|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Client[] findAll()
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
final class ClientRepository extends ServiceEntityRepository implements UserProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function loadUserByUsername(string $token): UserInterface
    {
        return $this->_em->createQuery(
            <<<'DQL'
                SELECT u 
                FROM App\Entity\Client u 
                WHERE u.token = :token
            DQL
        )
            ->setParameter('token', $token, Types::STRING)
            ->getSingleResult();
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass(string $class)
    {
        return $class === Client::class;
    }
}
