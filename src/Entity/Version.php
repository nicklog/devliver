<?php

namespace Shapecode\Devliver\Entity;

use Composer\Package\AliasPackage;
use Composer\Package\Loader\ArrayLoader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Class Version
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Shapecode\Devliver\Repository\VersionRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"package_id", "name"})
 * })
 */
class Version extends BaseEntity
{

    /**
     * @var Package
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Package", inversedBy="versions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $package;

    /**
     * @var ArrayCollection|PersistentCollection|Collection|Download[]
     * @ORM\OneToMany(targetEntity="Shapecode\Devliver\Entity\Download", mappedBy="version", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $downloads;

    /**
     * @var ArrayCollection|PersistentCollection|User[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\User", mappedBy="accessVersions", cascade={"persist"})
     */
    protected $accessUsers;

    /**
     * @var ArrayCollection|PersistentCollection|Author[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Author", inversedBy="versions", cascade={"persist"})
     */
    protected $authors;

    /**
     * @var ArrayCollection|PersistentCollection|Tag[]
     * @ORM\ManyToMany(targetEntity="Shapecode\Devliver\Entity\Tag", inversedBy="versions", cascade={"persist"})
     */
    protected $tags;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $data;

    /**
     */
    public function __construct()
    {
        parent::__construct();

        $this->accessUsers = new ArrayCollection();
        $this->authors = new ArrayCollection();
        $this->downloads = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return Package
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return ArrayCollection|PersistentCollection|Author[]
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    /**
     * @param Author $author
     *
     * @return bool
     */
    public function hasAuthor(Author $author): bool
    {
        return $this->getAuthors()->contains($author);
    }

    /**
     * @param Author $author
     */
    public function addAuthor(Author $author): void
    {
        if (!$this->hasAuthor($author)) {
            $this->getAuthors()->add($author);
        }
    }

    /**
     * @param Author $author
     */
    public function removeAuthor(Author $author): void
    {
        if ($this->hasAuthor($author)) {
            $this->getAuthors()->removeElement($author);
        }
    }

    /**
     * @return ArrayCollection|PersistentCollection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     *
     * @return bool
     */
    public function hasTag(Tag $tag): bool
    {
        return $this->getTags()->contains($tag);
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->hasTag($tag)) {
            $this->getTags()->add($tag);
        }
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag): void
    {
        if ($this->hasTag($tag)) {
            $this->getTags()->removeElement($tag);
        }
    }

    /**
     * @return \Composer\Package\PackageInterface
     */
    public function getPackageInformation(): \Composer\Package\PackageInterface
    {
        $loader = new ArrayLoader();

        $p = $loader->load($this->getData());

        if ($p instanceof AliasPackage) {
            $p = $p->getAliasOf();
        }

        return $p;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getPackage()->getName() . ' - ' . $this->getName();
    }
}
