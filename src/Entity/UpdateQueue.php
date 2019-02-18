<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UpdateQueue
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\UpdateQueueRepository")
 */
class UpdateQueue extends BaseEntity
{

    /**
     * @var Package
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
     * @inheritdoc
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    /**
     * @inheritdoc
     */
    public function getLockedAt(): ?\DateTime
    {
        return $this->lockedAt;
    }

    /**
     * @inheritdoc
     */
    public function setLockedAt(?\DateTime $lockedAt): void
    {
        $this->lockedAt = $lockedAt;
    }

    /**
     * @inheritdoc
     */
    public function getLastCalledAt(): \DateTime
    {
        return $this->lastCalledAt;
    }

    /**
     * @inheritdoc
     */
    public function setLastCalledAt(\DateTime $lastCalledAt): void
    {
        $this->lastCalledAt = $lastCalledAt;
    }
}
