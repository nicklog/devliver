<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Package
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\PackageRepository")
 */
class Package extends BaseEntity implements PackageInterface
{

    /**
     * @var ArrayCollection|PersistentCollection|Repo[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Repo", mappedBy="packages", cascade={"persist", "remove"})
     */
    protected $repos;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected $downloads = 0;

    /**
     */
    public function __construct()
    {
        $this->repos = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|PersistentCollection|Repo[]
     */
    public function getRepos(): Collection
    {
        return $this->repos;
    }

    /**
     * @param Repo $repo
     *
     * @return bool
     */
    public function hasRepo(Repo $repo): bool
    {
        return $this->repos->contains($repo);
    }

    /**
     * @param Repo $repo
     */
    public function addRepo(Repo $repo)
    {
        if (!$this->hasRepo($repo)) {
            $repo->getPackages()->add($this);
            $this->getRepos()->add($repo);
        }
    }

    /**
     * @param Repo $repo
     */
    public function removeRepo(Repo $repo)
    {
        if (!$this->hasRepo($repo)) {
            $repo->getPackages()->removeElement($this);
            $this->getRepos()->removeElement($repo);
        }
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getDownloads(): int
    {
        return $this->downloads;
    }

    /**
     * @param int $downloads
     */
    public function setDownloads(int $downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * @param int $downloads
     */
    public function increaseDownloads(int $downloads = 1)
    {
        $this->downloads += $downloads;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
