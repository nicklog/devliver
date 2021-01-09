<?php

declare(strict_types=1);

namespace Collection;

use App\Collection\VersionCollection;
use App\Entity\Version;
use Carbon\Carbon;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Collection\VersionCollection
 */
final class VersionCollectionTest extends TestCase
{
    public function testSortByReleaseDate(): void
    {
        $packageA = $this->createMock(PackageInterface::class);
        $packageA->method('isDev')->willReturn(false);
        $packageA->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-05 12:00:00'));

        $packageB = $this->createMock(PackageInterface::class);
        $packageB->method('isDev')->willReturn(false);
        $packageB->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-10 12:00:00'));

        $packageC = $this->createMock(PackageInterface::class);
        $packageC->method('isDev')->willReturn(true);
        $packageC->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-15 12:00:00'));

        $versionA = $this->createMock(Version::class);
        $versionA->method('getPackageInformation')->willReturn($packageA);

        $versionB = $this->createMock(Version::class);
        $versionB->method('getPackageInformation')->willReturn($packageB);

        $versionC = $this->createMock(Version::class);
        $versionC->method('getPackageInformation')->willReturn($packageC);

        $collection = VersionCollection::create(
            $versionA,
            $versionB,
            $versionC,
        );

        self::assertSame([
            $versionB,
            $versionA,
            $versionC,
        ], $collection->sortByReleaseDate()->toArray());
    }

    public function testToMapCollection(): void
    {
        $packageA = $this->createMock(PackageInterface::class);
        $packageA->method('isDev')->willReturn(false);
        $packageA->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-05 12:00:00'));

        $packageB = $this->createMock(PackageInterface::class);
        $packageB->method('isDev')->willReturn(false);
        $packageB->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-10 12:00:00'));

        $packageC = $this->createMock(PackageInterface::class);
        $packageC->method('isDev')->willReturn(true);
        $packageC->method('getReleaseDate')->willReturn(Carbon::parse('2020-05-15 12:00:00'));

        $versionA = $this->createMock(Version::class);
        $versionA->method('getPackageInformation')->willReturn($packageA);

        $versionB = $this->createMock(Version::class);
        $versionB->method('getPackageInformation')->willReturn($packageB);

        $versionC = $this->createMock(Version::class);
        $versionC->method('getPackageInformation')->willReturn($packageC);

        $collection = VersionCollection::create(
            $versionA,
            $versionB,
            $versionC,
        );

        self::assertSame([
            $packageA,
            $packageB,
            $packageC,
        ], $collection->toPackageCollection()->toArray());
    }
}
