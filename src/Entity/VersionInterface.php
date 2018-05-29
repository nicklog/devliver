<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface VersionInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface VersionInterface extends BaseEntityInterface
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
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @return \Composer\Package\PackageInterface
     */
    public function getPackageInformation(): \Composer\Package\PackageInterface;
}
