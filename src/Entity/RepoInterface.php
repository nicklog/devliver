<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface RepoInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface RepoInterface extends BaseEntityInterface
{

    /**
     * @return Package|null
     */
    public function getPackage(): ?Package;

    /**
     * @param Package $package
     */
    public function setPackage(Package $package): void;

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
