<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Sonata\UserBundle\Entity\BaseUser;

/**
 * Class User
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\UserRepository")
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
     * @var ArrayCollection|PersistentCollection|Repo[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Repo", mappedBy="creator", cascade={"persist"})
     */
    protected $repos;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->repos = new ArrayCollection();
    }

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
