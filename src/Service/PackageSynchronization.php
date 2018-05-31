<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\Dumper\ArrayDumper;
use Shapecode\Devliver\Composer\ComposerManager;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Version;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * Class PackageSynchronization
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class PackageSynchronization implements PackageSynchronizationInterface
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ComposerManager */
    protected $composerManager;

    /** @var RepositoryHelper */
    protected $repositoryHelper;

    /**
     * @param ManagerRegistry  $registry
     * @param ComposerManager  $composerManager
     * @param RepositoryHelper $repositoryHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper)
    {
        $this->registry = $registry;
        $this->composerManager = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
    }

    /**
     * @inheritdoc
     */
    public function syncAll(IOInterface $io = null): void
    {
        if ($io === null) {
            $io = $this->composerManager->createIO();
        }

        $repository = $this->registry->getRepository(Package::class);
        $packages = $repository->findAll();

        foreach ($packages as $package) {
            $this->sync($package, $io);
        }
    }

    /**
     * @inheritdoc
     */
    public function sync(PackageInterface $package, IOInterface $io = null): void
    {
        if ($io === null) {
            $io = $this->composerManager->createIO();
        }

        $repository = $this->composerManager->createRepository($package, $io);
        $packages = $repository->getPackages();

        if (!$packages) {
            return;
        }

        $this->save($package, $packages);
    }

    /**
     * @inheritdoc
     */
    public function save(PackageInterface $package, array $packages): void
    {
        if (count($packages)) {
            $em = $this->registry->getManager();

            $package->setLastUpdate(new \DateTime());

            $em->persist($package);
            $em->flush();

            $this->updateVersions($packages, $package);
        }
    }

    /**
     * @param array            $packages
     * @param PackageInterface $dbPackage
     */
    protected function updateVersions(array $packages, PackageInterface $dbPackage)
    {
        $versionRepository = $this->registry->getRepository(Version::class);
        $em = $this->registry->getManager();
        $dumper = new ArrayDumper();

        $knownVersions = $versionRepository->findBy([
            'package' => $dbPackage
        ]);

        $toRemove = [];
        foreach ($knownVersions as $knownVersion) {
            $name = $knownVersion->getName();
            $toRemove[$name] = $knownVersion;
        }

        foreach ($packages as $package) {
            if ($package instanceof AliasPackage) {
                $package = $package->getAliasOf();
            }

            $dbVersion = $versionRepository->findOneBy([
                'package' => $dbPackage->getId(),
                'name'    => $package->getPrettyVersion(),
            ]);

            $new = false;
            if (!$dbVersion) {
                $dbVersion = new Version();
                $dbVersion->setName($package->getPrettyVersion());
                $dbVersion->setPackage($dbPackage);
                $new = true;
            }

            $distUrl = $this->repositoryHelper->getComposerDistUrl($package->getPrettyName(), $package->getSourceReference());

            $package->setDistUrl($distUrl);
            $package->setDistType('zip');
            $package->setDistReference($package->getSourceReference());

            $packageData = $dumper->dump($package);

            $dbVersion->setData($packageData);

            $version = $package->getPrettyVersion();
            if (isset($toRemove[$version])) {
                unset($toRemove[$version]);
            }

            $em->persist($dbVersion);

            if ($new) {
                $em->flush();
            }
        }

        foreach ($toRemove as $item) {
            $em->remove($item);
        }

        $em->flush();
    }
}