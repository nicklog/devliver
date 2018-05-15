<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Repo
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\RepoRepository")
 */
class Repo extends BaseEntity implements RepoInterface
{

    /**
     * @var ArrayCollection|PersistentCollection|Package[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Package", inversedBy="repos", cascade={"persist", "remove"})
     */
    protected $packages;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "vcs"})
     */
    protected $type = 'vcs';

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $url;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->packages = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @inheritdoc
     */
    public function hasPackages()
    {
        return ($this->getPackages()->count() > 0);
    }

    /**
     * @inheritdoc
     */
    public function hasPackage(Package $package)
    {
        return $this->packages->contains($package);
    }

    /**
     * @inheritdoc
     */
    public function addPackage(Package $package)
    {
        if (!$this->hasPackage($package)) {
            $package->getRepos()->add($this);
            $this->getPackages()->add($package);
        }
    }

    /**
     * @inheritdoc
     */
    public function removePackage(Package $package)
    {
        if (!$this->hasPackage($package)) {
            $package->getRepos()->removeElement($this);
            $this->getPackages()->removeElement($package);
        }
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

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return implode(', ', $this->getPackages()->toArray());
    }
}
