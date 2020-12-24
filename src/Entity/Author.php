<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 */
class Author extends AbstractEntity
{
    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $name = null;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $email = null;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $homepage = null;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $role = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Version", mappedBy="authors")
     *
     * @var ArrayCollection|PersistentCollection|Collection|Version[]
     */
    protected $versions;

    public function __construct()
    {
        parent::__construct();

        $this->versions = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }
}
