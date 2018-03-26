<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Symfony\Component\HttpFoundation\Request;

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
     * @param IOInterface       $io
     * @param CompletePackage[] $packages
     */
    public function sync(IOInterface $io, array $packages);

    /**
     * @param Request $request
     * @param         $name
     * @param         $ref
     * @param         $format
     *
     * @return mixed|string
     */
    public function getDistFilename(Request $request, $name, $ref, $format): string;
}
