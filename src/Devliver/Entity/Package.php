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
     * @var ArrayCollection|PersistentCollection|Repo[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Repo", mappedBy="packages", cascade={"persist", "remove"})
     */
    protected $repos;

    /**
     * @var ArrayCollection|PersistentCollection|Version[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Version", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $versions;

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

    /** @var CompletePackage[] */
    protected $packages;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->repos = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getRepos(): Collection
    {
        return $this->repos;
    }

    /**
     * @inheritdoc
     */
    public function hasRepo(Repo $repo): bool
    {
        return $this->repos->contains($repo);
    }

    /**
     * @inheritdoc
     */
    public function addRepo(Repo $repo)
    {
        if (!$this->hasRepo($repo)) {
            $repo->getPackages()->add($this);
            $this->getRepos()->add($repo);
        }
    }

    /**
     * @inheritdoc
     */
    public function removeRepo(Repo $repo)
    {
        if (!$this->hasRepo($repo)) {
            $repo->getPackages()->removeElement($this);
            $this->getRepos()->removeElement($repo);
        }
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getDownloads(): int
    {
        return $this->downloads;
    }

    /**
     * @inheritdoc
     */
    public function setDownloads(int $downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * @inheritdoc
     */
    public function increaseDownloads(int $downloads = 1)
    {
        $this->downloads += $downloads;
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
    public function hasVersions(): bool
    {
        return ($this->getVersions()->count() > 0);
    }

    /**
     * @inheritdoc
     */
    public function hasVersion(VersionInterface $version)
    {
        return $this->getVersions()->contains($version);
    }

    /**
     * @inheritdoc
     */
    public function addVersion(VersionInterface $version)
    {
        if (!$this->hasVersion($version)) {
            $version->setPackage($this);
            $this->getVersions()->add($version);
        }
    }

    /**
     * @inheritdoc
     */
    public function removePackage(VersionInterface $version)
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
        if (!is_null($this->packages)) {
            return $this->packages;
        }

        $versions = $this->getVersions();
        $packages = [];

        foreach ($versions as $version) {
            $packages[] = $version->getPackageInformation();
        }

        $this->packages = ComposerUtil::sortPackagesByVersion($packages);

        return $this->packages;
    }

    /**
     * @inheritdoc
     */
    public function getLastStablePackage(): CompletePackage
    {
        return ComposerUtil::getLastStableVersion($this->getPackages());
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
