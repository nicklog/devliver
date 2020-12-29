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

use function sprintf;

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
    private Package $package;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Download", mappedBy="version", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection<int, Download>
     */
    private Collection $downloads;

    /** @ORM\Column(type="string") */
    private string $name;

    /**
     * @ORM\Column(type="json")
     *
     * @var mixed[]
     */
    private array $data = [];

    public function __construct(
        Package $package,
        PackageInterface $composerPackage
    ) {
        parent::__construct();

        $this->package   = $package;
        $this->name      = $composerPackage->getPrettyVersion();
        $this->downloads = new ArrayCollection();
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
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
     * @return Collection<int, Download>
     */
    public function getDownloads(): Collection
    {
        return $this->downloads;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->getPackage()->getName(), $this->getName());
    }
}
