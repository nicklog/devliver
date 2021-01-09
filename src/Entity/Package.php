<?php

declare(strict_types=1);

namespace App\Entity;

use App\Collection\PackageCollection;
use App\Collection\VersionCollection;
use App\Entity\Common\AbstractEntity;
use Carbon\Carbon;
use Composer\Package\PackageInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PackageRepository")
 */
class Package extends AbstractEntity
{
    /** @ORM\Column(type="string", options={"default": "vcs"}) */
    private string $type;

    /** @ORM\Column(type="string") */
    private string $url;

    /** @ORM\Column(type="string") */
    private string $name = '';

    /** @ORM\Column(type="boolean", options={"default": true}) */
    private bool $enable = true;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    private bool $abandoned = false;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $replacementPackage = null;

    /** @ORM\Column(type="text", nullable=true) */
    private ?string $readme = null;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    private bool $autoUpdate = false;

    /** @ORM\Column(type="datetimeutc") */
    private DateTime $lastUpdate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Version", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection<int, Version>
     */
    private Collection $versions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Download", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection<int, Download>
     */
    private Collection $downloads;

    private ?VersionCollection $versionsSorted = null;

    private ?PackageCollection $packages = null;

    private ?PackageInterface $lastStable = null;

    public function __construct(
        string $type,
        string $url,
    ) {
        parent::__construct();

        $this->url  = $url;
        $this->type = $type;

        $this->versions   = new ArrayCollection();
        $this->downloads  = new ArrayCollection();
        $this->lastUpdate = Carbon::now();
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
     * @return string[]
     */
    public function getConfig(): array
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
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
     * @return Collection<int, Download>
     */
    public function getDownloads(): Collection
    {
        return $this->downloads;
    }

    public function getVersions(): VersionCollection
    {
        return VersionCollection::create(...$this->versions->toArray());
    }

    public function getVersionsSorted(): VersionCollection
    {
        if ($this->versionsSorted === null) {
            $this->versionsSorted = $this->getVersions()->sortByReleaseDate();
        }

        return $this->versionsSorted;
    }

    public function getPackages(): PackageCollection
    {
        if ($this->packages === null) {
            $this->packages = $this->getVersionsSorted()->toPackageCollection();
        }

        return $this->packages;
    }

    public function getLastStablePackage(): ?PackageInterface
    {
        if ($this->lastStable === null) {
            $this->lastStable = $this->getPackages()->getLastStablePackage();
        }

        return $this->lastStable;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
