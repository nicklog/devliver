<?php

declare(strict_types=1);

namespace App\Collection;

use App\Entity\Version;
use Ramsey\Collection\AbstractCollection;

use function uasort;

final class VersionCollection extends AbstractCollection
{
    public static function create(Version ...$versions): self
    {
        return new self($versions);
    }

    public function sortByReleaseDate(): self
    {
        $versions = $this->toArray();

        uasort($versions, static function (Version $aVersion, Version $bVersion) {
            $a = $aVersion->getPackageInformation();
            $b = $bVersion->getPackageInformation();

            if ($a->isDev() && ! $b->isDev()) {
                return 1;
            }

            if (! $a->isDev() && $b->isDev()) {
                return -1;
            }

            if ($a->getReleaseDate() > $b->getReleaseDate()) {
                return -1;
            }

            if ($a->getReleaseDate() < $b->getReleaseDate()) {
                return 1;
            }

            return 0;
        });

        return self::create(...$versions);
    }

    public function toPackageCollection(): PackageCollection
    {
        $packages = $this->map(static fn (Version $version) => $version->getPackageInformation());

        return new PackageCollection($packages->toArray());
    }

    public function getType(): string
    {
        return Version::class;
    }
}
