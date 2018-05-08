<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface DownloadInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 */
interface DownloadInterface extends BaseEntityInterface
{

    /**
     * @return VersionInterface
     */
    public function getVersion(): VersionInterface;

    /**
     * @param VersionInterface $version
     */
    public function setVersion(VersionInterface $version);
}
