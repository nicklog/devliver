<?php

namespace Shapecode\Devliver\Util;

use Composer\Package\CompletePackage;
use Shapecode\Devliver\Entity\Version;

/**
 * Class ComposerUtil
 *
 * @package Shapecode\Devliver\Util
 * @author  Nikita Loges
 */
class ComposerUtil
{

    /**
     * @param Version[] $versions
     *
     * @return Version[]
     */
    public static function sortPackagesByVersion(array $versions): array
    {
        uasort($versions, function (Version $a, Version $b) {
            $a = $a->getPackageInformation();
            $b = $b->getPackageInformation();

            if ($a->isDev() && !$b->isDev()) {
                return 1;
            }

            if (!$a->isDev() && $b->isDev()) {
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
     * @param array $packages
     *
     * @return CompletePackage
     */
    public static function getLastStableVersion(array $packages): CompletePackage
    {
        foreach ($packages as $p) {
            if (!$p->isDev()) {
                return $p;
            }
        }

        return $packages[0];
    }
}
