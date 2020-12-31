<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ApiProvider implements UserProviderInterface
{
    public function loadUserByUsername(string $username): User
    {
        return new User('api_user', $username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}
