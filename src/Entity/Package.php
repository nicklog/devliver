<?php

namespace Shapecode\Devliver\Entity;

use Composer\Package\CompletePackage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Shapecode\Devliver\Util\ComposerUtil;

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
     * @var Repo
     * @ORM\OneToOne(targetEntity="Shapecode\Devliver\Entity\Repo", inversedBy="package", cascade={"persist"})
     */
    protected $repo;

    /**
     * @var ArrayCollection|PersistentCollection|Version[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Version", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $versions;

    /** @var ArrayCollection|Version[] */
    protected $versionsSorted;

    /**
     * @var ArrayCollection|PersistentCollection|Collection|Download[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Download", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $downloads;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $readme;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $autoUpdate = false;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdate;

    /** @var CompletePackage[] */
    protected $packages;

    /** @var CompletePackage */
    protected $lastStable;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->versions = new ArrayCollection();
        $this->downloads = new ArrayCollection();
        $this->lastUpdate = new \DateTime();
    }

    /**
     * @return Repo
     */
    public function getRepo(): Repo
    {
        return $this->repo;
    }

    /**
     * @param Repo $repo
     */
    public function setRepo(Repo $repo): void
    {
        $this->repo = $repo;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getReadme(): ?string
    {
        return $this->readme;
    }

    /**
     * @param string|null $readme
     */
    public function setReadme(?string $readme): void
    {
        $this->readme = $readme;
    }

    /**
     * @return bool
     */
    public function isAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }

    /**
     * @param bool $autoUpdate
     */
    public function setAutoUpdate(bool $autoUpdate): void
    {
        $this->autoUpdate = $autoUpdate;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate(\DateTime $lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * @inheritdoc
     */
    public function getVersionsSorted(): Collection
    {
        if ($this->versionsSorted === null) {
            $sorted = $this->getVersions()->toArray();
            $sorted = ComposerUtil::sortPackagesByVersion($sorted);

            $this->versionsSorted = new ArrayCollection($sorted);
        }

        return $this->versionsSorted;
    }

    /**
     * @inheritdoc
     */
    public function hasVersions(): bool
    {
        return ($this->getVersions()->count() > 0);
    }

    /**
     * @inheritdoc
     */
    public function hasVersion(VersionInterface $version): bool
    {
        return $this->getVersions()->contains($version);
    }

    /**
     * @inheritdoc
     */
    public function addVersion(VersionInterface $version): void
    {
        if (!$this->hasVersion($version)) {
            $version->setPackage($this);
            $this->getVersions()->add($version);
        }
    }

    /**
     * @inheritdoc
     */
    public function removePackage(VersionInterface $version): void
    {
        if (!$this->hasVersion($version)) {
            $this->getVersions()->removeElement($version);
        }
    }

    /**
     * @inheritdoc
     */
    public function getPackages(): array
    {
        if ($this->packages !== null) {
            return $this->packages;
        }

        $versions = $this->getVersionsSorted();

        $packages = [];

        foreach ($versions as $version) {
            $packages[] = $version->getPackageInformation();
        }

        $this->packages = $packages;

        return $this->packages;
    }

    /**
     * @inheritdoc
     */
    public function getLastStablePackage(): CompletePackage
    {
        if ($this->lastStable === null) {
            $this->lastStable = ComposerUtil::getLastStableVersion($this->getPackages());
        }

        return $this->lastStable;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
