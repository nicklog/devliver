<?php

namespace Shapecode\Devliver\Entity;

use Composer\Package\AliasPackage;
use Composer\Package\Loader\ArrayLoader;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Version
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity()
 */
class Version extends BaseEntity implements VersionInterface
{

    /**
     * @var PackageInterface
     * @ORM\ManyToOne(targetEntity="Shapecode\Devliver\Entity\Package", inversedBy="versions", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $package;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $data;

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    /**
     * @param PackageInterface $package
     */
    public function setPackage(PackageInterface $package)
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
}
