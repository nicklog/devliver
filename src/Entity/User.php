<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends AbstractEntity implements UserInterface
{
    /** @ORM\Column(type="string") */
    private string $email;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $password = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $passwordResetToken = null;

    /** @ORM\Column(type="datetimeutc", nullable=true) */
    private ?DateTimeInterface $passwordResetTokenExpireAt = null;

    /** @ORM\Column(type="string", nullable=true, unique=true) */
    protected ?string $apiToken = null;

    /** @ORM\Column(type="string", nullable=true, unique=true) */
    protected ?string $repositoryToken = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Package", mappedBy="creator", cascade={"persist"})
     *
     * @var Collection<int, Package>
     */
    protected Collection $createdPackages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Package", inversedBy="accessUsers")
     *
     * @var Collection<int, Package>
     */
    protected Collection $accessPackages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Version", inversedBy="accessUsers")
     *
     * @var Collection<int, Version>
     */
    protected Collection $accessVersions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Repo", mappedBy="creator", cascade={"persist"})
     *
     * @var Collection<int, Repo>
     */
    protected Collection $repos;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    protected bool $packageRootAccess = true;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    protected bool $autoAddToNewPackages = true;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    protected bool $autoAddToNewVersions = true;

    public function __construct(
        string $email
    ) {
        parent::__construct();

        $this->email           = $email;
        $this->createdPackages = new ArrayCollection();
        $this->accessPackages  = new ArrayCollection();
        $this->accessVersions  = new ArrayCollection();
        $this->repos           = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getPasswordResetTokenExpireAt(): ?DateTimeInterface
    {
        return $this->passwordResetTokenExpireAt;
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Package[]
     */
    public function getCreatedPackages(): Collection
    {
        return $this->createdPackages;
    }

    public function hasCreatedPackage(Package $package): bool
    {
        return $this->getCreatedPackages()->contains($package);
    }

    public function addCreatedPackage(Package $package): void
    {
        if ($this->hasCreatedPackage($package)) {
            return;
        }

        $package->setCreator($this);
        $this->getCreatedPackages()->add($package);
    }

    public function removeCreatedPackage(Package $package): void
    {
        if (! $this->hasCreatedPackage($package)) {
            return;
        }

        $package->setCreator(null);
        $this->getCreatedPackages()->removeElement($package);
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Package[]
     */
    public function getAccessPackages(): Collection
    {
        $packages = $this->accessPackages;

        foreach ($this->getAccessVersions() as $version) {
            $package = $version->getPackage();
            if ($packages->contains($package)) {
                continue;
            }

            $packages->add($package);
        }

        return $packages;
    }

    public function hasAccessPackage(Package $package): bool
    {
        return $this->getAccessPackages()->contains($package);
    }

    public function addAccessPackage(Package $package): void
    {
        if ($this->hasAccessPackage($package)) {
            return;
        }

        $this->getAccessPackages()->add($package);
    }

    public function removeAccessPackage(Package $package): void
    {
        if (! $this->hasAccessPackage($package)) {
            return;
        }

        $this->getCreatedPackages()->removeElement($package);
    }

    /**
     * @return ArrayCollection|PersistentCollection|Collection|Version[]
     */
    public function getAccessVersions(): Collection
    {
        return $this->accessVersions;
    }

    public function hasAccessVersion(Version $version): bool
    {
        return $this->getAccessVersions()->contains($version);
    }

    public function addAccessVersion(Version $version): void
    {
        if ($this->hasAccessVersion($version)) {
            return;
        }

        $this->getAccessVersions()->add($version);
    }

    public function removeAccessVersion(Version $version): void
    {
        if (! $this->hasAccessVersion($version)) {
            return;
        }

        $this->getCreatedPackages()->removeElement($version);
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    public function getRepositoryToken(): ?string
    {
        return $this->repositoryToken;
    }

    public function setRepositoryToken(?string $repositoryToken): void
    {
        $this->repositoryToken = $repositoryToken;
    }

    public function isPackageRootAccess(): bool
    {
        return $this->packageRootAccess;
    }

    public function setPackageRootAccess(bool $packageRootAccess): void
    {
        $this->packageRootAccess = $packageRootAccess;
    }

    public function isAutoAddToNewPackages(): bool
    {
        return $this->autoAddToNewPackages;
    }

    public function setAutoAddToNewPackages(bool $autoAddToNewPackages): void
    {
        $this->autoAddToNewPackages = $autoAddToNewPackages;
    }

    public function isAutoAddToNewVersions(): bool
    {
        return $this->autoAddToNewVersions;
    }

    public function setAutoAddToNewVersions(bool $autoAddToNewVersions): void
    {
        $this->autoAddToNewVersions = $autoAddToNewVersions;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return [
            'ROLE_USER',
        ];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
