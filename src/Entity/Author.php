<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Author
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\AuthorRepository")
 */
class Author extends BaseEntity implements AuthorInterface
{

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $email;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $homepage;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $role;

    /**
     * @var ArrayCollection|PersistentCollection|Collection|Version[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Version", mappedBy="authors")
     */
    protected $versions;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->versions = new ArrayCollection();
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    /**
     * @param null|string $homepage
     */
    public function setHomepage(?string $homepage): void
    {
        $this->homepage = $homepage;
    }

    /**
     * @return null|string
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param null|string $role
     */
    public function setRole(?string $role): void
    {
        $this->role = $role;
    }
}
