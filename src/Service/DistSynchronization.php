<?php

declare(strict_types=1);

namespace App\Service;

use App\Composer\ComposerManager;
use App\Entity\Package as EntityPackage;
use Composer\Downloader\FileDownloader;
use Composer\Factory;
use Composer\Package\Archiver\ArchiveManager;
use Composer\Package\CompletePackage;
use Composer\Util\ComposerMirror;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

use function assert;
use function dirname;
use function file_exists;
use function sys_get_temp_dir;

class DistSynchronization
{
    public const DIST_FORMAT = '/%package%/%reference%.%type%';

    protected ComposerManager $composerManager;

    protected string $format = 'zip';

    protected string $distDir;

    public function __construct(ComposerManager $composerManager, string $distDir)
    {
        $this->composerManager = $composerManager;
        $this->distDir         = $distDir;
    }

    /**
     * @inheritdoc
     */
    public function getDistFilename(EntityPackage $dbPackage, $ref): string
    {
        $cacheFile = $this->getCacheFile($dbPackage, $ref);

        if (file_exists($cacheFile)) {
            return $cacheFile;
        }

        if (! $dbPackage->getVersions()->count()) {
            return '';
        }

        foreach ($dbPackage->getPackages() as $package) {
            if ($package instanceof CompletePackage && $package->getSourceReference() === $ref) {
                $this->downloadPackage($dbPackage, $package);

                return $cacheFile;
            }
        }

        return '';
    }

    protected function downloadPackage(EntityPackage $dbPackage, CompletePackage $package): string
    {
        $cacheFile = $this->getCacheFile($dbPackage, $package->getSourceReference());
        $cacheDir  = dirname($cacheFile);

        $fs = new Filesystem();

        if (! $fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }

        if (! $fs->exists($cacheFile)) {
            $url = $package->getDistUrl();

            if ($url !== null) {
                try {
                    $downloader = $this->createFileDownloader();
                    $path       = $downloader->download($package, sys_get_temp_dir());

                    $fs->rename($path, $cacheFile);

                    return $cacheFile;
                } catch (Throwable $e) {
                }
            }

            $archiveManager = $this->createArchiveManager();
            $path           = $archiveManager->archive($package, $package->getDistType() ?: $this->format, sys_get_temp_dir());

            $fs->rename($path, $cacheFile);
        }

        return $cacheFile;
    }

    protected function getCacheFile(EntityPackage $dbPackage, string $ref): string
    {
        $targetDir = $this->distDir . self::DIST_FORMAT;

        $name    = $dbPackage->getName();
        $type    = $this->format;
        $version = 'placeholder';

        return ComposerMirror::processUrl($targetDir, $name, $version, $ref, $type);
    }

    protected function createFileDownloader(): FileDownloader
    {
        $config = $this->composerManager->getConfig();
        $io     = $this->composerManager->createIO();

        $downloader = new FileDownloader($io, $config);
        $downloader->setOutputProgress(false);

        return $downloader;
    }

    protected function createArchiveManager(): ArchiveManager
    {
        $config = $this->composerManager->getConfig();

        $factory = new Factory();

        $archiveManager = $factory->createArchiveManager($config);
        assert($archiveManager instanceof ArchiveManager);
        $archiveManager->setOverwriteFiles(false);

        return $archiveManager;
    }
}
