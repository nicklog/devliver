<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method User|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method User[] findAll()
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[] findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
final class UserRepository extends ServiceEntityRepository implements UserProviderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByUsernameAndToken(string $username, string $token): ?User
    {
        return $this->findOneBy([
            'username' => $username,
            'apiToken' => $token,
        ]);
    }

    public function countAll(): int
    {
        return (int) $this->_em->createQuery(
            <<<'DQL'
                SELECT COUNT(1) 
                FROM App\Entity\User u 
            DQL
        )
            ->getSingleScalarResult();
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $result = $this->_em->createQuery(
            <<<'DQL'
                SELECT u 
                FROM App\Entity\User u 
                WHERE u.email = :username
            DQL
        )
            ->setParameter('username', $username, Types::STRING)
            ->getOneOrNullResult();

        if ($result === null) {
            throw new UsernameNotFoundException();
        }

        return $result;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}
