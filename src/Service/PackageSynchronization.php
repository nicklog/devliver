<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\Dumper\ArrayDumper;
use Shapecode\Devliver\Composer\ComposerManager;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Entity\Version;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var ComposerManager */
    protected $composerManager;

    /** @var string */
    protected $packageDir;

    /** @var ArrayDumper */
    protected $dumper;

    /** @var TagAwareAdapterInterface */
    protected $cache;

    /**
     * @param ManagerRegistry          $registry
     * @param UrlGeneratorInterface    $router
     * @param ComposerManager          $composerManager
     * @param TagAwareAdapterInterface $cache
     * @param                          $packageDir
     */
    public function __construct(ManagerRegistry $registry, UrlGeneratorInterface $router, ComposerManager $composerManager, TagAwareAdapterInterface $cache, $packageDir)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->composerManager = $composerManager;
        $this->packageDir = $packageDir;
        $this->cache = $cache;
        $this->dumper = new ArrayDumper;
    }

    /**
     * @inheritdoc
     */
    public function sync(PackageInterface $package, IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->composerManager->createIO();
        }

        $repositories = $this->composerManager->createReposByPackage($io, $package);

        $packages = [];
        foreach ($repositories as $repository) {
            $packages = array_merge($packages, $repository->getPackages());
        }

        if (!$packages) {
            return;
        }

        $this->save($packages);
    }

    /**
     * @inheritdoc
     */
    public function save(array $packages, Repo $repo = null)
    {
        $packageRepository = $this->registry->getRepository(Package::class);
        $em = $this->registry->getManager();

        if (count($packages)) {
            foreach ($packages as $package) {
                if ($package instanceof AliasPackage) {
                    $package = $package->getAliasOf();
                }

                $dbPackage = $packageRepository->findOneBy([
                    'name' => $package->getPrettyName()
                ]);

                if (!$dbPackage) {
                    $dbPackage = new Package();
                    $dbPackage->setName($package->getPrettyName());

                    $em->persist($dbPackage);
                    $em->flush();
                }

                if ($repo) {
                    $repo->addPackage($dbPackage);
                }
            }

            if ($repo) {
                $em->persist($repo);
                $em->flush();
            }

            $this->updateVersions($packages, $dbPackage);
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

            $distUrl = $this->getComposerDistUrl($package->getPrettyName(), $package->getSourceReference());

            $package->setDistUrl($distUrl);
            $package->setDistType('zip');
            $package->setDistReference($package->getSourceReference());

            $packageData = $this->dumper->dump($package);

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

    /**
     * @inheritdoc
     */
    public function dumpPackageJson(User $user, PackageInterface $package): string
    {
        $data = [];

        foreach ($package->getVersions() as $version) {
            $packageData = $this->dumper->dump($version->getPackageInformation());

            $data[$version->getName()] = $packageData;
        }

        $jsonData = ['packages' => [$package->getName() => $data]];

        return json_encode($jsonData);
    }

    /**
     * @inheritdoc
     */
    public function dumpPackagesJson(User $user): string
    {
        $cacheKey = 'user-packages-json-' . $user->getId();
        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $item->tag('packages.json');
        $item->tag('packages-user-' . $user->getId());

        $repos = $this->registry->getRepository(Repo::class)->findAll();
        $providers = [];

        foreach ($repos as $repo) {
            $item->tag('packages-repo-' . $repo->getId());

            if ($repo->hasPackages()) {

                foreach ($repo->getPackages() as $package) {
                    $name = $package->getName();

                    $item->tag('packages-package-' . $package->getId());

                    if (!isset($providers[$name])) {
                        $providers[$name] = [
                            'sha256' => null
                        ];
                    }
                }

                continue;
            }
        }

        $repo = [];

        $distUrl = $this->getComposerDistUrl('%package%', '%reference%', '%type%');
        $mirror = [
            'dist-url'  => $distUrl,
            'preferred' => true,
        ];

        $repo['packages'] = [];
        $repo['notify-batch'] = $this->router->generate('devliver_download_track', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $repo['mirrors'] = [$mirror];
        $repo['providers-url'] = $this->router->generate('devliver_repository_provider_base', [], UrlGeneratorInterface::ABSOLUTE_URL) . '/%package%.json';
        $repo['providers'] = $providers;

        $json = json_encode($repo);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    /**
     * @param                       $package
     * @param                       $ref
     * @param                       $type
     *
     * @return string
     */
    protected function getComposerDistUrl($package, $ref, $type = 'zip'): string
    {
        $distUrl = $this->router->generate('devliver_repository_dist', [
            'vendor'  => 'PACK',
            'project' => 'AGE',
            'ref'     => 'REF',
            'type'    => 'TYPE',
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $distUrl = str_replace(
            ['PACK/AGE', 'REF', 'TYPE'],
            [$package, $ref, $type],
            $distUrl
        );

        return $distUrl;
    }
}