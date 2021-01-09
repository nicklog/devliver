<?php

declare(strict_types=1);

namespace Collection;

use App\Collection\PackageCollection;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Collection\PackageCollection
 */
final class PackageCollectionTest extends TestCase
{
    public function testLastStable(): void
    {
        $packageDev = $this->createMock(PackageInterface::class);
        $packageDev->method('isDev')->willReturn(true);

        $packageStable = $this->createMock(PackageInterface::class);
        $packageStable->method('isDev')->willReturn(false);

        $collection = PackageCollection::create(
            $packageDev,
            $packageStable
        );

        self::assertSame($packageStable, $collection->getLastStablePackage());
    }

    public function testLastStableNotFound(): void
    {
        $packageDev = $this->createMock(PackageInterface::class);
        $packageDev->method('isDev')->willReturn(true);

        $packageStable = $this->createMock(PackageInterface::class);
        $packageStable->method('isDev')->willReturn(true);
        $collection = PackageCollection::create(
            $packageDev,
            $packageStable
        );

        self::assertNull($collection->getLastStablePackage());
    }
}
