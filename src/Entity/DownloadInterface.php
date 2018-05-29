<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface DownloadInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface DownloadInterface extends BaseEntityInterface
{

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface;

    /**
     * @param PackageInterface $package
     */
    public function setPackage(PackageInterface $package);

    /**
     * @return VersionInterface|null
     */
    public function getVersion();

    /**
     * @param VersionInterface $version
     */
    public function setVersion(VersionInterface $version);

    /**
     * @return string
     */
    public function getVersionName();

    /**
     * @param string $versionName
     */
    public function setVersionName(string $versionName);
}
