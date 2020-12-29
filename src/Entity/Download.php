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
    private Package $package;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Version", inversedBy="downloads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Version $version = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $versionName;

    public function __construct(
        Package $package,
        ?string $versionName
    ) {
        parent::__construct();

        $this->package     = $package;
        $this->versionName = $versionName;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersionName(): ?string
    {
        return $this->versionName;
    }
}
