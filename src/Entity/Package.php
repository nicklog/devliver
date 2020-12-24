<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use App\Util\ComposerUtil;
use Composer\Package\CompletePackage;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PackageRepository")
 */
class Package extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="createdPackages", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected ?User $creator = null;

    /** @ORM\Column(type="string", options={"default": "vcs"}) */
    protected string $type = 'vcs';

    /** @ORM\Column(type="string") */
    protected string $url;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    protected bool $enable = true;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    protected bool $abandoned = false;

    /** @ORM\Column(type="string", nullable=true) */
    protected ?string $replacementPackage = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Repo", inversedBy="package", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?Repo $repo = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Version", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|PersistentCollection|Version[]
     */
    protected $versions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Download", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|PersistentCollection|Collection|Download[]
     */
    protected $downloads;

    /** @ORM\Column(type="string") */
    protected string $name;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $readme = null;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    protected bool $autoUpdate = false;

    /** @ORM\Column(type="datetimeutc") */
    protected ?DateTime $lastUpdate = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="accessPackages", cascade={"persist"})
     *
     * @var ArrayCollection|PersistentCollection|User[]
     */
    protected $accessUsers;

    /** @var ArrayCollection|Version[] */
    protected $versionsSorted;

    /** @var CompletePackage[] */
    protected array $packages;

    protected CompletePackage $lastStable;

    public function __construct()
    {
        parent::__construct();

        $this->versions    = new ArrayCollection();
        $this->downloads   = new ArrayCollection();
        $this->accessUsers = new ArrayCollection();
        $this->lastUpdate  = new DateTime();
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    public function isAbandoned(): bool
    {
        return $this->abandoned;
    }

    public function setAbandoned(bool $abandoned): void
    {
        $this->abandoned = $abandoned;
    }

    public function getReplacementPackage(): ?string
    {
        return $this->replacementPackage;
    }

    public function setReplacementPackage(?string $replacementPackage): void
    {
        $this->replacementPackage = $replacementPackage;
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
    }

    public function getRepo(): ?Repo
    {
        return $this->repo;
    }

    public function setRepo(?Repo $repo): void
    {
        $this->repo = $repo;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getReadme(): ?string
    {
        return $this->readme;
    }

    public function setReadme(?string $readme): void
    {
        $this->readme = $readme;
    }

    public function isAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }

    public function setAutoUpdate(bool $autoUpdate): void
    {
        $this->autoUpdate = $autoUpdate;
    }

    public function getLastUpdate(): ?DateTime
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(DateTime $lastUpdate): void
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

    public function hasVersions(): bool
    {
        return $this->getVersions()->count() > 0;
    }

    public function hasVersion(Version $version): bool
    {
        return $this->getVersions()->contains($version);
    }

    public function addVersion(Version $version): void
    {
        if ($this->hasVersion($version)) {
            return;
        }

        $version->setPackage($this);
        $this->getVersions()->add($version);
    }

    public function removePackage(Version $version): void
    {
        if ($this->hasVersion($version)) {
            return;
        }

        $this->getVersions()->removeElement($version);
    }

    /**
     * @return mixed[]
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

    public function getLastStablePackage(): CompletePackage
    {
        if ($this->lastStable === null) {
            $this->lastStable = ComposerUtil::getLastStableVersion($this->getPackages());
        }

        return $this->lastStable;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
