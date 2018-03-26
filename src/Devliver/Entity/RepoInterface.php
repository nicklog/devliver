<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

/**
 * Interface RepoInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface RepoInterface extends BaseEntityInterface
{

    /**
     * @return ArrayCollection|PersistentCollection|Package[]
     */
    public function getPackages();

    /**
     * @return bool
     */
    public function hasPackages();

    /**
     * @param Package $package
     *
     * @return bool
     */
    public function hasPackage(Package $package);

    /**
     * @param Package $package
     */
    public function addPackage(Package $package);

    /**
     * @param Package $package
     */
    public function removePackage(Package $package);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     */
    public function setUrl($url);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return string
     */
    public function __toString();
}
