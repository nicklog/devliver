<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated
 *
 * @ORM\Entity(repositoryClass="App\Repository\RepoRepository")
 */
class Repo extends AbstractEntity
{
    /** @ORM\OneToOne(targetEntity="App\Entity\Package", mappedBy="repo", cascade={"persist", "remove"}, orphanRemoval=true) */
    protected Package $package;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="repos", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected ?User $creator = null;

    /** @ORM\Column(type="string", options={"default": "vcs"}) */
    protected string $type = 'vcs';

    /** @ORM\Column(type="string") */
    protected string $url;

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'type' => $this->getType(),
            'url'  => $this->getUrl(),
        ];
    }
}
