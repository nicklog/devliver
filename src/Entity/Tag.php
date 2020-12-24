<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag extends AbstractEntity
{
    /** @ORM\Column(type="string", unique=true) */
    protected string $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Version", mappedBy="tags")
     *
     * @var ArrayCollection|PersistentCollection|Collection|Version[]
     */
    protected $versions;

    public function __construct()
    {
        parent::__construct();

        $this->versions = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
