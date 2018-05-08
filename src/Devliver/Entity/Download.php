<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Download
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @ORM\Entity()
 */
class Download extends BaseEntity implements DownloadInterface
{

    /**
     * @var VersionInterface
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Version", inversedBy="downloads", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $version;

    /**
     * @return VersionInterface
     */
    public function getVersion(): VersionInterface
    {
        return $this->version;
    }

    /**
     * @param VersionInterface $version
     */
    public function setVersion(VersionInterface $version)
    {
        $this->version = $version;
    }
}
