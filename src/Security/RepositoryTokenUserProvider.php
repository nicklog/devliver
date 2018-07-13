<?php

namespace Shapecode\Devliver\Security;

use Shapecode\Devliver\Entity\User;

/**
 * Class RepositoryTokenUserProvider
 *
 * @package Shapecode\Devliver\Security
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class RepositoryTokenUserProvider extends TokenUserProvider
{

    /**
     * @param $token
     *
     * @return null
     */
    public function getUsernameForToken($token)
    {
        /** @var User $user */
        $user = $this->userManager->findUserBy([
            'repositoryToken' => $token
        ]);

        if ($user) {
            return $user->getUsername();
        }

        return null;
    }

}
