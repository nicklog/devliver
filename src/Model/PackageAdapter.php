<?php

declare(strict_types=1);

namespace App\Model;

use Composer\Package\CompletePackageInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use function call_user_func_array;
use function explode;
use function method_exists;

final class PackageAdapter
{
    protected CompletePackageInterface $package;

    public function __construct(CompletePackageInterface $package)
    {
        $this->package = $package;
    }

    public function getVendorName(): mixed
    {
        $split = explode('/', $this->getPackage()->getName());

        return $split[0];
    }

    public function getProjectName(): mixed
    {
        $split = explode('/', $this->getPackage()->getName());

        return $split[1];
    }

    public function getAlias(): ?string
    {
        $extra   = $this->getPackage()->getExtra();
        $version = $this->getPackage()->getPrettyVersion();

        return $extra['branch-alias'][$version] ?? null;
    }

    public function getVersionName(): string
    {
        $alias = $this->getAlias();

        return $alias ?? $this->getPackage()->getPrettyVersion();
    }

    public function getPackage(): CompletePackageInterface
    {
        return $this->package;
    }

    public function __get(mixed $id): mixed
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($this->getPackage(), $id);
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->getPackage(), $name)) {
            return call_user_func_array([$this->getPackage(), $name], $arguments);
        }

        return $this->__get($name);
    }
}
