<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Tag
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\TagRepository")
 */
class Tag extends BaseEntity
{

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * @var ArrayCollection|PersistentCollection|Collection|Version[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Version", mappedBy="tags")
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
