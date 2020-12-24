<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class TokenUserProvider implements UserProviderInterface
{
    protected UserRepository $userManager;

    public function __construct(UserRepository $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return null
     */
    abstract public function getUsernameForToken(string $token): ?string;

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        return $this->userManager->findOneByUsernameAndToken($username);
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return $class === User::class;
    }
}
