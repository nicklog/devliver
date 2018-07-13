<?php

namespace Shapecode\Devliver\Security;

use Shapecode\Devliver\Entity\User;

/**
 * Class ApiTokenUserProvider
 *
 * @package Shapecode\Devliver\Security
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class ApiTokenUserProvider extends TokenUserProvider
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
            'apiToken' => $token
        ]);

        if ($user) {
            return $user->getUsername();
        }

        return null;
    }

}
