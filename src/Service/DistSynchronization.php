<?php

declare(strict_types=1);

namespace App\Service;

use App\Composer\ComposerManager;
use App\Entity\Package as EntityPackage;
use Composer\Downloader\FileDownloader;
use Composer\IO\NullIO;
use Composer\Package\CompletePackage;
use Composer\Util\ComposerMirror;
use Composer\Util\HttpDownloader;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

use function dirname;
use function file_exists;
use function sys_get_temp_dir;

final class DistSynchronization
{
    public const DIST_FORMAT = '/%package%/%reference%.%type%';

    private ComposerManager $composerManager;

    private string $format = 'zip';

    private string $distDir;

    public function __construct(ComposerManager $composerManager, string $distDir)
    {
        $this->composerManager = $composerManager;
        $this->distDir         = $distDir;
    }

    public function getDistFilename(EntityPackage $dbPackage, string $ref): ?string
    {
        $cacheFile = $this->getCacheFile($dbPackage, $ref);

        if (file_exists($cacheFile)) {
            return $cacheFile;
        }

        if ($dbPackage->getVersions()->count() === 0) {
            return null;
        }

        foreach ($dbPackage->getPackages() as $package) {
            if ($package instanceof CompletePackage && $package->getSourceReference() === $ref) {
                $this->downloadPackage($dbPackage, $package);

                return $cacheFile;
            }
        }

        return null;
    }

    private function downloadPackage(EntityPackage $dbPackage, CompletePackage $package): string
    {
        $cacheFile = $this->getCacheFile($dbPackage, $package->getSourceReference());
        $cacheDir  = dirname($cacheFile);

        $fs = new Filesystem();

        if (! $fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }

        if (! $fs->exists($cacheFile)) {
            try {
                $downloader = $this->createFileDownloader();
                $path       = $downloader->download($package, sys_get_temp_dir());

                $fs->rename($path, $cacheFile);

                return $cacheFile;
            } catch (Throwable $e) {
            }

            $composer       = $this->composerManager->createComposer();
            $archiveManager = $composer->getArchiveManager();
            $path           = $archiveManager->archive($package, $package->getDistType() ?: $this->format, sys_get_temp_dir());

            $fs->rename($path, $cacheFile);
        }

        return $cacheFile;
    }

    private function getCacheFile(EntityPackage $dbPackage, string $ref): string
    {
        $targetDir = $this->distDir . self::DIST_FORMAT;

        $name    = $dbPackage->getName();
        $type    = $this->format;
        $version = 'placeholder';

        return ComposerMirror::processUrl($targetDir, $name, $version, $ref, $type);
    }

    private function createFileDownloader(): FileDownloader
    {
        $config = $this->composerManager->createComposer()->getConfig();
        $io     = new NullIO();

        $httpDownloader = new HttpDownloader($io, $config);

        return new FileDownloader($io, $config, $httpDownloader);
    }
}
