<?php

namespace Shapecode\Devliver\Util;

use Composer\Package\CompletePackage;

/**
 * Class ComposerUtil
 *
 * @package Shapecode\Devliver\Util
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class ComposerUtil
{

    /**
     * @param CompletePackage[] $packages
     *
     * @return CompletePackage[]
     */
    public static function sortPackagesByVersion(array $packages): array
    {
        uasort($packages, function (CompletePackage $a, CompletePackage $b) {
            if ($a->isDev() && !$b->isDev()) {
                return -1;
            } elseif (!$a->isDev() && $b->isDev()) {
                return 1;
            }

            if ($a->getReleaseDate() < $b->getReleaseDate()) {
                return 1;
            } elseif ($a->getReleaseDate() < $b->getReleaseDate()) {
                return -1;
            }

            return 0;
        });

        return $packages;
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
