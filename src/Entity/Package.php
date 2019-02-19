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
class Package extends BaseEntity
{

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\User", inversedBy="createdPackages", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $creator;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "vcs"})
     */
    protected $type = 'vcs';

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $url;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $enable = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $abandoned = false;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $replacementPackage;

    /**
     * @var Repo|null
     * @ORM\OneToOne(targetEntity="Shapecode\Devliver\Entity\Repo", inversedBy="package", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $repo;

    /**
     * @var ArrayCollection|PersistentCollection|Version[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Version", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $versions;

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
     * @ORM\Column(type="datetimeutc")
     */
    protected $lastUpdate;

    /**
     * @var ArrayCollection|PersistentCollection|User[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\User", mappedBy="accessPackages", cascade={"persist"})
     */
    protected $accessUsers;

    /** @var ArrayCollection|Version[] */
    protected $versionsSorted;

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
        $this->accessUsers = new ArrayCollection();
        $this->lastUpdate = new \DateTime();
    }

    /**
     * @return User|null
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     */
    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @return bool
     */
    public function isAbandoned(): bool
    {
        return $this->abandoned;
    }

    /**
     * @param bool $abandoned
     */
    public function setAbandoned(bool $abandoned): void
    {
        $this->abandoned = $abandoned;
    }

    /**
     * @return string|null
     */
    public function getReplacementPackage(): ?string
    {
        return $this->replacementPackage;
    }

    /**
     * @param string|null $replacementPackage
     */
    public function setReplacementPackage(?string $replacementPackage): void
    {
        $this->replacementPackage = $replacementPackage;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
    }

    /**
     * @return Repo|null
     */
    public function getRepo(): ?Repo
    {
        return $this->repo;
    }

    /**
     * @param Repo|null $repo
     */
    public function setRepo(?Repo $repo): void
    {
        $this->repo = $repo;
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
     * @return Collection|Version[]
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * @return Collection|Version[]
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
     * @return bool
     */
    public function hasVersions(): bool
    {
        return ($this->getVersions()->count() > 0);
    }

    /**
     * @param Version $version
     *
     * @return bool
     */
    public function hasVersion(Version $version): bool
    {
        return $this->getVersions()->contains($version);
    }

    /**
     * @param Version $version
     */
    public function addVersion(Version $version): void
    {
        if (!$this->hasVersion($version)) {
            $version->setPackage($this);
            $this->getVersions()->add($version);
        }
    }

    /**
     * @param Version $version
     */
    public function removePackage(Version $version): void
    {
        if (!$this->hasVersion($version)) {
            $this->getVersions()->removeElement($version);
        }
    }

    /**
     * @return array
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
     * @return CompletePackage
     */
    public function getLastStablePackage(): CompletePackage
    {
        if ($this->lastStable === null) {
            $this->lastStable = ComposerUtil::getLastStableVersion($this->getPackages());
        }

        return $this->lastStable;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
