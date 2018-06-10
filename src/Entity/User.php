<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var string|null
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $apiToken;

    /**
     * @var ArrayCollection|PersistentCollection|Package[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Package", mappedBy="creator", cascade={"persist"})
     */
    protected $createdPackages;

    /**
     * @var ArrayCollection|PersistentCollection|Version[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Version", inversedBy="accessUsers", cascade={"persist"})
     */
    protected $accessVersions;

    /**
     * @var ArrayCollection|PersistentCollection|Repo[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Repo", mappedBy="creator", cascade={"persist"})
     */
    protected $repos;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $packageRootAccess = true;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $autoAddToNewPackages = true;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $autoAddToNewVersions = true;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->createdPackages = new ArrayCollection();
        $this->accessVersions = new ArrayCollection();
        $this->repos = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Package[]
     */
    public function getCreatedPackages(): Collection
    {
        return $this->createdPackages;
    }

    /**
     * @param Package $package
     *
     * @return bool
     */
    public function hasCreatedPackages(Package $package): bool
    {
        return $this->getCreatedPackages()->contains($package);
    }

    /**
     * @param Package $package
     */
    public function addCreatedPackages(Package $package): void
    {
        if (!$this->hasCreatedPackages($package)) {
            $package->setCreator($this);
            $this->getCreatedPackages()->add($package);
        }
    }

    /**
     * @param Package $package
     */
    public function removeCreatedPackages(Package $package): void
    {
        if ($this->hasCreatedPackages($package)) {
            $package->setCreator(null);
            $this->getCreatedPackages()->removeElement($package);
        }
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Version[]
     */
    public function getAccessVersions(): Collection
    {
        return $this->accessVersions;
    }

    /**
     * @return ArrayCollection|Package[]
     */
    public function getAccessPackages(): ArrayCollection
    {
        $packages = new ArrayCollection();

        foreach ($this->getAccessVersions() as $accessVersion) {
            $package = $accessVersion->getPackage();
            if (!$packages->contains($package)) {
                $packages->add($package);
            }
        }

        return $packages;
    }

    /**
     * @param Version $version
     *
     * @return bool
     */
    public function hasAccessVersions(Version $version): bool
    {
        return $this->getAccessVersions()->contains($version);
    }

    /**
     * @param Version $version
     */
    public function addAccessVersions(Version $version): void
    {
        if (!$this->hasAccessVersions($version)) {
            $this->getAccessVersions()->add($version);
        }
    }

    /**
     * @param Version $version
     */
    public function removeAccessVersions(Version $version): void
    {
        if ($this->hasAccessVersions($version)) {
            $this->getCreatedPackages()->removeElement($version);
        }
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

    /**
     * @return bool
     */
    public function isPackageRootAccess(): bool
    {
        return $this->packageRootAccess;
    }

    /**
     * @param bool $packageRootAccess
     */
    public function setPackageRootAccess(bool $packageRootAccess): void
    {
        $this->packageRootAccess = $packageRootAccess;
    }

    /**
     * @return bool
     */
    public function isAutoAddToNewPackages(): bool
    {
        return $this->autoAddToNewPackages;
    }

    /**
     * @param bool $autoAddToNewPackages
     */
    public function setAutoAddToNewPackages(bool $autoAddToNewPackages): void
    {
        $this->autoAddToNewPackages = $autoAddToNewPackages;
    }

    /**
     * @return bool
     */
    public function isAutoAddToNewVersions(): bool
    {
        return $this->autoAddToNewVersions;
    }

    /**
     * @param bool $autoAddToNewVersions
     */
    public function setAutoAddToNewVersions(bool $autoAddToNewVersions): void
    {
        $this->autoAddToNewVersions = $autoAddToNewVersions;
    }

}
