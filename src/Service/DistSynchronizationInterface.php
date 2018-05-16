<?php

namespace Shapecode\Devliver\Service;

use Shapecode\Devliver\Entity\PackageInterface;

/**
 * Interface DistSynchronizationInterface
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
interface DistSynchronizationInterface
{

    const DIST_FORMAT = '/%package%/%version%/%reference%.%type%';

    /**
     * @param PackageInterface $dbPackage
     * @param                  $ref
     *
     * @return string
     */
    public function getDistFilename(PackageInterface $dbPackage, $ref): string;
}
