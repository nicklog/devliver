<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DownloadRepository")
 */
class Download extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Package", inversedBy="downloads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Package $package;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Version", inversedBy="downloads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected Version $version;

    /** @ORM\Column(type="string", nullable=true) */
    protected ?string $versionName = null;

    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function setPackage(Package $package)
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
    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    public function getVersionName(): string
    {
        return $this->versionName;
    }

    public function setVersionName(string $versionName): void
    {
        $this->versionName = $versionName;
    }
}
