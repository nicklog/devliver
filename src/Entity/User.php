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
     * @var string|null
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $repositoryToken;

    /**
     * @var ArrayCollection|PersistentCollection|Package[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Package", mappedBy="creator", cascade={"persist"})
     */
    protected $createdPackages;

    /**
     * @var ArrayCollection|PersistentCollection|Package[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Package", inversedBy="accessUsers")
     */
    protected $accessPackages;

    /**
     * @var ArrayCollection|PersistentCollection|Version[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Version", inversedBy="accessUsers")
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
        $this->accessPackages = new ArrayCollection();
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
    public function hasCreatedPackage(Package $package): bool
    {
        return $this->getCreatedPackages()->contains($package);
    }

    /**
     * @param Package $package
     */
    public function addCreatedPackage(Package $package): void
    {
        if (!$this->hasCreatedPackage($package)) {
            $package->setCreator($this);
            $this->getCreatedPackages()->add($package);
        }
    }

    /**
     * @param Package $package
     */
    public function removeCreatedPackage(Package $package): void
    {
        if ($this->hasCreatedPackage($package)) {
            $package->setCreator(null);
            $this->getCreatedPackages()->removeElement($package);
        }
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Package[]
     */
    public function getAccessPackages(): Collection
    {
        $packages = $this->accessPackages;

        foreach ($this->getAccessVersions() as $version) {
            $package = $version->getPackage();
            if (!$packages->contains($package)) {
                $packages->add($package);
            }
        }

        return $packages;
    }

    /**
     * @param PackageInterface $package
     *
     * @return bool
     */
    public function hasAccessPackage(PackageInterface $package): bool
    {
        return $this->getAccessPackages()->contains($package);
    }

    /**
     * @param PackageInterface $package
     */
    public function addAccessPackage(PackageInterface $package): void
    {
        if (!$this->hasAccessPackage($package)) {
            $this->getAccessPackages()->add($package);
        }
    }

    /**
     * @param PackageInterface $package
     */
    public function removeAccessPackage(PackageInterface $package): void
    {
        if ($this->hasAccessPackage($package)) {
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
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function hasAccessVersion(VersionInterface $version): bool
    {
        return $this->getAccessVersions()->contains($version);
    }

    /**
     * @param VersionInterface $version
     */
    public function addAccessVersion(VersionInterface $version): void
    {
        if (!$this->hasAccessVersion($version)) {
            $this->getAccessVersions()->add($version);
        }
    }

    /**
     * @param VersionInterface $version
     */
    public function removeAccessVersion(VersionInterface $version): void
    {
        if ($this->hasAccessVersion($version)) {
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
     * @return null|string
     */
    public function getRepositoryToken(): ?string
    {
        return $this->repositoryToken;
    }

    /**
     * @param null|string $repositoryToken
     */
    public function setRepositoryToken(?string $repositoryToken): void
    {
        $this->repositoryToken = $repositoryToken;
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
