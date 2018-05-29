<?php

namespace Shapecode\Devliver\Repository;

use Doctrine\ORM\EntityRepository;
use Shapecode\Devliver\Entity\User;

/**
 * Class UserRepository
 *
 * @package Shapecode\Devliver\Repository
 * @author  Nikita Loges
 */
class UserRepository extends EntityRepository
{

    /**
     * @param string $username
     * @param string $token
     *
     * @return null|User
     */
    public function findOneByUsernameAndToken(string $username, string $token): ?User
    {
        return $this->findOneBy([
            'username' => $username,
            'apiToken' => $token
        ]);
    }

}
