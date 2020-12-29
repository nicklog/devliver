<?php

declare(strict_types=1);

namespace App\Entity\Common;

use DateTimeInterface;

interface ModifiedAt
{
    public function getCreated(): DateTimeInterface;

    public function setUpdated(DateTimeInterface $updated): void;

    public function getUpdated(): ?DateTimeInterface;
}
