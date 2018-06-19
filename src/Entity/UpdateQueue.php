<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UpdateQueue
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\UpdateQueueRepository")
 */
class UpdateQueue extends BaseEntity
{

    /**
     * @var PackageInterface
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Package")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $package;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetimeutc", nullable=true)
     */
    protected $lockedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimeutc", nullable=false)
     */
    protected $lastCalledAt;

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    /**
     * @param PackageInterface $package
     */
    public function setPackage(PackageInterface $package): void
    {
        $this->package = $package;
    }

    /**
     * @return \DateTime|null
     */
    public function getLockedAt(): ?\DateTime
    {
        return $this->lockedAt;
    }

    /**
     * @param \DateTime|null $lockedAt
     */
    public function setLockedAt(?\DateTime $lockedAt): void
    {
        $this->lockedAt = $lockedAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastCalledAt(): \DateTime
    {
        return $this->lastCalledAt;
    }

    /**
     * @param \DateTime $lastCalledAt
     */
    public function setLastCalledAt(\DateTime $lastCalledAt): void
    {
        $this->lastCalledAt = $lastCalledAt;
    }
}
