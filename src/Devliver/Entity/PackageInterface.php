<?php

namespace Shapecode\Devliver\Entity;

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
     * @return ArrayCollection|PersistentCollection|Collection|Repo[]
     */
    public function getRepos(): Collection;

    /**
     * @param Repo $repo
     *
     * @return bool
     */
    public function hasRepo(Repo $repo): bool;

    /**
     * @param Repo $repo
     */
    public function addRepo(Repo $repo);

    /**
     * @param Repo $repo
     */
    public function removeRepo(Repo $repo);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return int
     */
    public function getDownloads(): int;

    /**
     * @param int $downloads
     */
    public function setDownloads(int $downloads);

    /**
     * @param int $downloads
     */
    public function increaseDownloads(int $downloads = 1);
}
