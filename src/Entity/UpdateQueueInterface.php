<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface UpdateQueueInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 */
interface UpdateQueueInterface extends BaseEntityInterface
{

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface;

    /**
     * @param PackageInterface $package
     */
    public function setPackage(PackageInterface $package): void;

    /**
     * @return \DateTime|null
     */
    public function getLockedAt(): ?\DateTime;

    /**
     * @param \DateTime|null $lockedAt
     */
    public function setLockedAt(?\DateTime $lockedAt): void;

    /**
     * @return \DateTime
     */
    public function getLastCalledAt(): \DateTime;

    /**
     * @param \DateTime $lastCalledAt
     */
    public function setLastCalledAt(\DateTime $lastCalledAt): void;
}
