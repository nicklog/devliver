<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use DateTime;
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
    protected ?DateTime $lockedAt = null;

    /** @ORM\Column(type="datetimeutc", nullable=false) */
    protected DateTime $lastCalledAt;

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getLockedAt(): ?DateTime
    {
        return $this->lockedAt;
    }

    public function setLockedAt(?DateTime $lockedAt): void
    {
        $this->lockedAt = $lockedAt;
    }

    public function getLastCalledAt(): DateTime
    {
        return $this->lastCalledAt;
    }

    public function setLastCalledAt(DateTime $lastCalledAt): void
    {
        $this->lastCalledAt = $lastCalledAt;
    }
}
