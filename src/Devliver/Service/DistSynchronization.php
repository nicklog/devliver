<?php

namespace Shapecode\Devliver\Service;

use Composer\Config as ComposerConfig;
use Composer\Downloader\FileDownloader;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Archiver\ArchiveManager;
use Composer\Package\PackageInterface;
use Composer\Util\ComposerMirror;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Model\PackageAdapter;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DistSynchronization
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class DistSynchronization implements DistSynchronizationInterface
{

    /** @var ComposerConfig */
    protected $config;

    /** @var string */
    protected $format = 'zip';

    /** @var string */
    protected $distDir;

    /**
     * @param ComposerConfig $config
     * @param                $distDir
     */
    public function __construct(ComposerConfig $config, $distDir)
    {
        $this->config = $config;
        $this->distDir = $distDir;
    }

    /**
     * @inheritdoc
     */
    public function getDistFilename(Package $dbPackage, $ref): string
    {
        if (!$dbPackage->getVersions()->count()) {
            return '';
        }

        foreach ($dbPackage->getPackages() as $package) {
            if ($package->getSourceReference() == $ref) {

                $io = new NullIO();
                $io->loadConfiguration($this->config);

                $cacheFile = $this->getCacheFile($package);

                if (file_exists($cacheFile)) {
                    return $cacheFile;
                }

                $this->downloadPackage($package);

                return $cacheFile;
            }
        }

        return '';
    }

    /**
     * @param PackageInterface $package
     *
     * @return mixed|null
     */
    protected function downloadPackage(PackageInterface $package)
    {
        $cacheFile = $this->getCacheFile($package);
        $cacheDir = dirname($cacheFile);

        $fs = new Filesystem();

        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }

        if (!$fs->exists($cacheFile)) {

            if ($url = $package->getDistUrl()) {
                try {
                    $downloader = $this->createFileDownloader();
                    $path = $downloader->download($package, sys_get_temp_dir());

                    $fs->rename($path, $cacheFile);

                    return $cacheFile;
                } catch (\Exception $e) {
                }
            }

            $archiveManager = $this->createArchiveManager();
            $path = $archiveManager->archive($package, $package->getDistType() ?: $this->format, sys_get_temp_dir());

            $fs->rename($path, $cacheFile);
        }

        return $cacheFile;
    }

    /**
     * @param PackageInterface $package
     *
     * @return mixed
     */
    protected function getCacheFile(PackageInterface $package): string
    {
        $targetDir = $this->distDir . DistSynchronizationInterface::DIST_FORMAT;

        $adapter = new PackageAdapter($package);

        $name = $package->getName();
        $ref = $package->getDistReference() ?: $package->getSourceReference();
        $type = $package->getDistType() ?: $this->format;
        $version = $adapter->getVersionName();

        return ComposerMirror::processUrl($targetDir, $name, $version, $ref, $type);
    }

    /**
     * @return FileDownloader
     */
    protected function createFileDownloader(): FileDownloader
    {
        $config = $this->config;

        $io = new NullIO();
        $io->loadConfiguration($config);

        $downloader = new FileDownloader($io, $config);
        $downloader->setOutputProgress(false);

        return $downloader;
    }

    /**
     * @return ArchiveManager
     */
    protected function createArchiveManager(): ArchiveManager
    {
        $config = $this->config;

        $factory = new Factory();

        /* @var ArchiveManager $archiveManager */
        $archiveManager = $factory->createArchiveManager($config);
        $archiveManager->setOverwriteFiles(false);

        return $archiveManager;
    }
}
