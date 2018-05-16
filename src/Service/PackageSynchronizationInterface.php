<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Repo;
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
     * @param PackageInterface $package
     * @param IOInterface|null $io
     */
    public function sync(PackageInterface $package, IOInterface $io = null);

    /**
     * @param array|\Composer\Package\PackageInterface[] $packages
     * @param Repo|null                                  $repo
     */
    public function save(array $packages, Repo $repo = null);

    /**
     * @param User             $user
     * @param PackageInterface $package
     *
     * @return string
     */
    public function dumpPackageJson(User $user, PackageInterface $package): string;

    /**
     * @param User $user
     *
     * @return string
     */
    public function dumpPackagesJson(User $user): string;
}
