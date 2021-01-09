<?php

declare(strict_types=1);

namespace App\Collection;

use Composer\Package\PackageInterface;
use Ramsey\Collection\AbstractCollection;

/**
 * @method self filter(callable $callback)
 * @method PackageInterface first()
 */
final class PackageCollection extends AbstractCollection
{
    public static function create(PackageInterface ...$versions): self
    {
        return new self($versions);
    }

    public function getLastStablePackage(): ?PackageInterface
    {
        $packages = $this->filter(static fn (PackageInterface $package) => ! $package->isDev());

        foreach ($this->toArray() as $p) {
            if (! $p->isDev()) {
                return $p;
            }
        }

        if ($packages->isEmpty()) {
            return null;
        }

        return $packages->first();
    }

    public function getType(): string
    {
        return PackageInterface::class;
    }
}
