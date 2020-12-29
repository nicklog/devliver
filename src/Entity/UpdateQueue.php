<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UpdateQueueRepository")
 */
class UpdateQueue extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Package")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Package $package;

    /** @ORM\Column(type="datetimeutc", nullable=true) */
    protected ?DateTimeInterface $lockedAt = null;

    /** @ORM\Column(type="datetimeutc", nullable=false) */
    protected DateTimeInterface $lastCalledAt;

    public function __construct(
        Package $package,
        DateTimeInterface $lastCalledAt
    ) {
        parent::__construct();

        $this->package      = $package;
        $this->lastCalledAt = $lastCalledAt;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function getLockedAt(): ?DateTimeInterface
    {
        return $this->lockedAt;
    }

    public function setLockedAt(?DateTimeInterface $lockedAt): self
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }

    public function getLastCalledAt(): DateTimeInterface
    {
        return $this->lastCalledAt;
    }

    public function setLastCalledAt(DateTimeInterface $lastCalledAt): self
    {
        $this->lastCalledAt = $lastCalledAt;

        return $this;
    }
}
