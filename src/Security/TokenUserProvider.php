<?php

namespace Shapecode\Devliver\Security;

use FOS\UserBundle\Model\UserManager;
use Shapecode\Devliver\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class TokenUserProvider
 *
 * @package Shapecode\Devliver\Security
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class TokenUserProvider implements UserProviderInterface
{

    /** @var UserManager */
    protected $userManager;

    /**
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param $token
     *
     * @return null
     */
    public function getUsernameForToken($token)
    {
        /** @var User $user */
        $user = $this->userManager->findUserBy([
            'apiToken' => $token
        ]);

        if ($user) {
            return $user->getUsername();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        return $this->userManager->findUserByUsername($username);
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
        return User::class === $class;
    }

}
