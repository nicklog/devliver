<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Composer\Package\AliasPackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\PackageInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VersionRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"package_id", "name"})
 * })
 */
class Version extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Package", inversedBy="versions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Package $package;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Download", mappedBy="version", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|PersistentCollection|Collection|Download[]
     */
    protected $downloads;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="accessVersions", cascade={"persist"})
     *
     * @var ArrayCollection|PersistentCollection|User[]
     */
    protected $accessUsers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Author", inversedBy="versions", cascade={"persist"})
     *
     * @var ArrayCollection|PersistentCollection|Author[]
     */
    protected $authors;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="versions", cascade={"persist"})
     *
     * @var ArrayCollection|PersistentCollection|Tag[]
     */
    protected $tags;

    /** @ORM\Column(type="string") */
    protected ?string $name = null;

    /**
     * @ORM\Column(type="json")
     *
     * @var mixed[]
     */
    protected array $data;

    public function __construct()
    {
        parent::__construct();

        $this->accessUsers = new ArrayCollection();
        $this->authors     = new ArrayCollection();
        $this->downloads   = new ArrayCollection();
        $this->tags        = new ArrayCollection();
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param mixed[] $data
     */
    public function setData(array $data): void
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

    public function hasAuthor(Author $author): bool
    {
        return $this->getAuthors()->contains($author);
    }

    public function addAuthor(Author $author): void
    {
        if ($this->hasAuthor($author)) {
            return;
        }

        $this->getAuthors()->add($author);
    }

    public function removeAuthor(Author $author): void
    {
        if (! $this->hasAuthor($author)) {
            return;
        }

        $this->getAuthors()->removeElement($author);
    }

    /**
     * @return ArrayCollection|PersistentCollection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function hasTag(Tag $tag): bool
    {
        return $this->getTags()->contains($tag);
    }

    public function addTag(Tag $tag): void
    {
        if ($this->hasTag($tag)) {
            return;
        }

        $this->getTags()->add($tag);
    }

    public function removeTag(Tag $tag): void
    {
        if (! $this->hasTag($tag)) {
            return;
        }

        $this->getTags()->removeElement($tag);
    }

    public function getPackageInformation(): PackageInterface
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
