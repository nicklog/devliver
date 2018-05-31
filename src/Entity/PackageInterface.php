<?php

namespace Shapecode\Devliver\Entity;

use Composer\Package\CompletePackage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;

/**
 * Interface PackageInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface PackageInterface extends BaseEntityInterface
{

    /**
     * @return null|User
     */
    public function getCreator(): ?User;

    /**
     * @param null|User $creator
     */
    public function setCreator(?User $creator): void;

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     */
    public function setUrl($url);

    /**
     * @return bool
     */
    public function isEnable(): bool;

    /**
     * @return bool
     */
    public function isAbandoned(): bool;

    /**
     * @param bool $abandoned
     */
    public function setAbandoned(bool $abandoned): void;

    /**
     * @return string|null
     */
    public function getReplacementPackage(): ?string;

    /**
     * @param string|null $replacementPackage
     */
    public function setReplacementPackage(?string $replacementPackage): void;

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void;

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return Repo|null
     */
    public function getRepo(): ?Repo;

    /**
     * @param Repo|null $repo
     */
    public function setRepo(?Repo $repo): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @return bool
     */
    public function isAutoUpdate(): bool;

    /**
     * @param bool $autoUpdate
     */
    public function setAutoUpdate(bool $autoUpdate): void;

    /**
     * @return \DateTime|null
     */
    public function getLastUpdate(): ?\DateTime;

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate(\DateTime $lastUpdate): void;

    /**
     * @return ArrayCollection|PersistentCollection|Version[]
     */
    public function getVersions(): Collection;

    /**
     * @return ArrayCollection|Version[]
     */
    public function getVersionsSorted(): Collection;

    /**
     * @return bool
     */
    public function hasVersions(): bool;

    /**
     * @inheritdoc
     */
    public function addVersion(VersionInterface $version): void;

    /**
     * @inheritdoc
     */
    public function removePackage(VersionInterface $version): void;

    /**
     * @return CompletePackage[]
     */
    public function getPackages(): array;

    /**
     * @return CompletePackage
     */
    public function getLastStablePackage(): CompletePackage;
}
