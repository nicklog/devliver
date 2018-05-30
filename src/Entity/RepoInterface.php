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
     * @return null|User
     */
    public function getCreator(): ?User;

    /**
     * @param null|User $creator
     */
    public function setCreator(?User $creator): void;

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
     * @return bool
     */
    public function isEnable(): bool;

    /**
     * @return bool
     */
    public function isAbandoned(): bool;

    /**
     * @param bool $abandoned
     */
    public function setAbandoned(bool $abandoned): void;

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void;

    /**
     * @return array
     */
    public function getConfig();
}
