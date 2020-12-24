<?php

declare(strict_types=1);

namespace App\Util;

use App\Entity\Version;
use Composer\Package\CompletePackage;

use function uasort;

class ComposerUtil
{
    /**
     * @param Version[] $versions
     *
     * @return Version[]
     */
    public static function sortPackagesByVersion(array $versions): array
    {
        uasort($versions, static function (Version $a, Version $b) {
            $a = $a->getPackageInformation();
            $b = $b->getPackageInformation();

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

        return $versions;
    }

    /**
     * @param mixed[] $packages
     */
    public static function getLastStableVersion(array $packages): CompletePackage
    {
        foreach ($packages as $p) {
            if (! $p->isDev()) {
                return $p;
            }
        }

        return $packages[0];
    }
}
