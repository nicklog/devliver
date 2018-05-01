<?php

namespace Shapecode\Devliver\Service;

use Shapecode\Devliver\Entity\Package;

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
     * @param Package $dbPackage
     * @param         $ref
     *
     * @return string
     */
    public function getDistFilename(Package $dbPackage, $ref): string;
}
