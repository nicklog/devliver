<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Package\CompletePackageInterface;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\User;

/**
 * Interface PackageSynchronizationInterface
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
interface PackageSynchronizationInterface
{

    /**
     * @param IOInterface|null $io
     */
    public function syncAll(IOInterface $io = null): void;

    /**
     * @param PackageInterface $package
     * @param IOInterface|null $io
     */
    public function sync(PackageInterface $package, IOInterface $io = null): void;

    /**
     * @param PackageInterface                 $package
     * @param array|CompletePackageInterface[] $packages
     */
    public function save(PackageInterface $package, array $packages): void;
}
