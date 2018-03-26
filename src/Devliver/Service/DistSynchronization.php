<?php

namespace Shapecode\Devliver\Service;

use Composer\Config as ComposerConfig;
use Composer\Downloader\FileDownloader;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\Archiver\ArchiveManager;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\PackageInterface;
use Composer\Util\ComposerMirror;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DistSynchronization
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class DistSynchronization implements DistSynchronizationInterface
{

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var ComposerConfig */
    protected $config;

    /** @var RepositorySynchronizationInterface */
    protected $repositorySynchronization;

    /** @var PackageSynchronizationInterface */
    protected $packageSynchronization;

    /** @var string */
    protected $format = 'zip';

    /** @var string */
    protected $distDir;

    /**
     * @param UrlGeneratorInterface              $urlGenerator
     * @param ComposerConfig                     $config
     * @param RepositorySynchronizationInterface $repositorySynchronization
     * @param PackageSynchronizationInterface    $packageSynchronization
     * @param                                    $distDir
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ComposerConfig $config,
        RepositorySynchronizationInterface $repositorySynchronization,
        PackageSynchronizationInterface $packageSynchronization,
        $distDir)
    {
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->repositorySynchronization = $repositorySynchronization;
        $this->packageSynchronization = $packageSynchronization;
        $this->distDir = $distDir;
    }

    /**
     * @inheritdoc
     */
    public function sync(IOInterface $io, array $packages)
    {
        $config = $this->config;

        $downloader = new FileDownloader($io, $config);
        $downloader->setOutputProgress($io instanceof ConsoleIO);

        $factory = new Factory();
        /* @var ArchiveManager $archiveManager */
        $archiveManager = $factory->createArchiveManager($config);
        $archiveManager->setOverwriteFiles(false);

        /** @var PackageInterface[][] $packagesByName */
        $packagesByName = [];
        foreach ($packages as $package) {
            $packagesByName[$package->getName()][] = $package;
        }

        foreach ($packagesByName as $packages) {
            foreach ($packages as $package) {
                if ($package instanceof AliasPackage || $package->getType() === 'metapackage') {
                    continue;
                }

                if (!$package->getDistUrl() && !$package->getSourceUrl()) {
                    continue;
                }

                $cacheFile = $this->getCacheFile($package);

                $this->downloadPackage($downloader, $archiveManager, $package, $cacheFile);
            }
        }
    }

    /**
     * @param Request $request
     * @param         $name
     * @param         $ref
     * @param         $format
     *
     * @return mixed|string
     */
    public function getDistFilename(Request $request, $name, $ref, $format): string
    {
        $json = $this->packageSynchronization->getJsonMetadataPath($name);

        if (!file_exists($json)) {
            return '';
        }

        $packages = JsonFile::parseJson(file_get_contents($json), $json);

        if (empty($packages['packages'][$name])) {#
            return '';
        }

        foreach ($packages['packages'][$name] as $pVersion => $package) {
            if ($package['source']['reference'] == $ref) {
                $io = new NullIO();
                $io->loadConfiguration($this->config);

                $loader = new ArrayLoader();

                if (isset($package['source']) && $ref) {
                    $package['source']['reference'] = $ref;
                }

                if (isset($package['dist']) && $ref) {
                    $package['dist']['reference'] = $ref;
                }

                $package = $loader->load($package);
                if ($package instanceof AliasPackage) {
                    $package = $package->getAliasOf();
                }

                $cacheFile = $this->getCacheFile($package);

                if (file_exists($cacheFile)) {
                    return $cacheFile;
                }

                $this->sync($io, [$package]);

                return $cacheFile;
            }
        }

        return '';
    }

    /**
     * @param FileDownloader   $downloader
     * @param ArchiveManager   $archiveManager
     * @param PackageInterface $package
     * @param null             $cacheFile
     *
     * @return mixed|null
     */
    protected function downloadPackage(FileDownloader $downloader, ArchiveManager $archiveManager, PackageInterface $package, $cacheFile = null)
    {
        if (null === $cacheFile) {
            $cacheFile = $this->getCacheFile($package);
        }

        $cacheDir = dirname($cacheFile);

        $fs = new Filesystem();

        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }

        if (!$fs->exists($cacheFile)) {
            if ($url = $package->getDistUrl()) {
                try {
                    $path = $downloader->download($package, sys_get_temp_dir());

                    $fs->rename($path, $cacheFile);

                    return $cacheFile;
                } catch (\Exception $e) {
                }
            }

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
    protected function getCacheFile(PackageInterface $package)
    {
        $targetDir = $this->distDir . DistSynchronizationInterface::DIST_FORMAT;

        $name = $package->getName();
        $ref = $package->getDistReference() ?: $package->getSourceReference();
        $type = $package->getDistType() ?: $this->format;
        $version = $package->getVersion();

        return ComposerMirror::processUrl($targetDir, $name, $version, $ref, $type);
    }
}
