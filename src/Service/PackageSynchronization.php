<?php

declare(strict_types=1);

namespace App\Service;

use App\Composer\ComposerManager;
use App\Entity\Package;
use App\Entity\Version;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\Dumper\ArrayDumper;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;

use function count;

final class PackageSynchronization
{
    private ManagerRegistry $registry;

    private ComposerManager $composerManager;

    private RepositoryHelper $repositoryHelper;

    public function __construct(
        ManagerRegistry $registry,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper,
    ) {
        $this->registry         = $registry;
        $this->composerManager  = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
    }

    public function syncAll(?IOInterface $io = null): void
    {
        $repository = $this->registry->getRepository(Package::class);
        $packages   = $repository->findAll();

        foreach ($packages as $package) {
            $this->sync($package);
        }
    }

    public function sync(Package $package): void
    {
        $repository = $this->composerManager->createRepository($package);
        $packages   = $repository->getPackages();

        if (count($packages) === 0) {
            return;
        }

        $package->setReadme($this->repositoryHelper->getReadme($repository));

        $this->save($package, $packages);
    }

    /**
     * @inheritdoc
     */
    public function save(Package $package, array $packages): void
    {
        if (count($packages) === 0) {
            return;
        }

        $em = $this->registry->getManager();
        $package->setLastUpdate(new DateTime());

        $em->persist($package);
        $em->flush();

        $this->updateVersions($packages, $package);
    }

    /**
     * @param mixed[] $packages
     */
    private function updateVersions(array $packages, Package $dbPackage): void
    {
        $versionRepository = $this->registry->getRepository(Version::class);
        $em                = $this->registry->getManager();
        $dumper            = new ArrayDumper();

        $knownVersions = $versionRepository->findBy([
            'package' => $dbPackage,
        ]);

        $toRemove = [];
        foreach ($knownVersions as $knownVersion) {
            $name            = $knownVersion->getName();
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
            if ($dbVersion === null) {
                $dbVersion = new Version($dbPackage, $package);
                $new       = true;
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

            if (! $new) {
                continue;
            }

            $em->flush();
        }

        foreach ($toRemove as $item) {
            $em->remove($item);
        }

        $em->flush();
    }
}
