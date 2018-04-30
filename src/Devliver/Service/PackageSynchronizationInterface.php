<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Package\CompletePackageInterface;
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
     * @param array     $packages
     * @param Repo|null $repo
     */
    public function save(array $packages, Repo $repo = null);

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasJsonMetadata($name);

    /**
     * @param $name
     *
     * @return string
     */
    public function getJsonMetadataPath($name);

    /**
     * @param $packageName
     *
     * @return string
     */
    public function getJsonMetadata($packageName);

    /**
     * @param PackageInterface $package
     */
    public function runUpdate(PackageInterface $package);

    /**
     * @param $name
     *
     * @return array|CompletePackageInterface[]
     */
    public function loadPackages($name);

    /**
     * @param User $user
     *
     * @return string
     */
    public function dumpPackagesJson(User $user);
}
