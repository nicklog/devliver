<?php

namespace Shapecode\Devliver\Model;

use Composer\Package\CompletePackageInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class PackageAdapter
 *
 * @package Shapecode\Devliver\Model
 * @author  Nikita Loges
 */
class PackageAdapter
{

    /** @var CompletePackageInterface */
    protected $package;

    /**
     * @param CompletePackageInterface $package
     */
    public function __construct(CompletePackageInterface $package)
    {
        $this->package = $package;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        $split = explode('/', $this->getPackage()->getName());

        return $split[0];
    }

    /**
     * @return mixed
     */
    public function getProjectName()
    {
        $split = explode('/', $this->getPackage()->getName());

        return $split[1];
    }

    /**
     * @return null
     */
    public function getAlias()
    {
        $extra = $this->getPackage()->getExtra();
        $version = $this->getPackage()->getPrettyVersion();

        if (isset($extra['branch-alias'][$version])) {
            return $extra['branch-alias'][$version];
        }

        return null;
    }

    /**
     * @return null
     */
    public function getVersionName()
    {
        $alias = $this->getAlias();

        if (!empty($alias)) {
            return $alias;
        }

        return $this->getPackage()->getPrettyVersion();
    }

    /**
     * @return CompletePackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @inheritDoc
     */
    public function __get($id)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($this->getPackage(), $id);
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->getPackage(), $name)) {
            return call_user_func_array([$this->getPackage(), $name], $arguments);
        } else {
            return $this->__get($name);
        }
    }
}
