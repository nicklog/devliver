<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Download
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\DownloadRepository")
 */
class Download extends BaseEntity implements DownloadInterface
{

    /**
     * @var PackageInterface
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Package", inversedBy="downloads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $package;

    /**
     * @var VersionInterface
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Version", inversedBy="downloads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $version;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $versionName;

    /**
     * @inheritdoc
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function setPackage(PackageInterface $package)
    {
        $this->package = $package;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setVersion(VersionInterface $version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersionName()
    {
        return $this->versionName;
    }

    /**
     * @param string $versionName
     */
    public function setVersionName(string $versionName)
    {
        $this->versionName = $versionName;
    }
}
