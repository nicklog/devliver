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
     * @inheritdoc
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @inheritdoc
     */
    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @inheritdoc
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @inheritdoc
     */
    public function isAbandoned(): bool
    {
        return $this->abandoned;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRepo(): ?Repo
    {
        return $this->repo;
    }

    /**
     * @inheritdoc
     */
    public function setRepo(?Repo $repo): void
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
     * @inheritdoc
     */
    public function getReadme(): ?string
    {
        return $this->readme;
    }

    /**
     * @inheritdoc
     */
    public function setReadme(?string $readme): void
    {
        $this->readme = $readme;
    }

    /**
     * @inheritdoc
     */
    public function isAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }

    /**
     * @inheritdoc
     */
    public function setAutoUpdate(bool $autoUpdate): void
    {
        $this->autoUpdate = $autoUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @inheritdoc
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
