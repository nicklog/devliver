<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;

/**
 * Class User
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity()
 */
class User extends BaseUser
{

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $apiToken;

    /**
     * @return string|null
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     */
    public function setApiToken(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

}
