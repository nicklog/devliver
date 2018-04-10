<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Loader\ArrayLoader;
use Shapecode\Devliver\Composer\ComposerFactory;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Model\PackageAdapter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
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

    /** @var ComposerFactory */
    protected $composerFactory;

    /** @var string */
    protected $packageDir;

    /** @var ArrayDumper */
    protected $dumper;

    /**
     * @param ManagerRegistry       $registry
     * @param UrlGeneratorInterface $router
     * @param ComposerFactory       $composerFactory
     * @param                       $packageDir
     */
    public function __construct(ManagerRegistry $registry, UrlGeneratorInterface $router, ComposerFactory $composerFactory, $packageDir)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->composerFactory = $composerFactory;
        $this->packageDir = $packageDir;
        $this->dumper = new ArrayDumper;
    }

    /**
     * @inheritdoc
     */
    public function sync(PackageInterface $package, IOInterface $io = null)
    {
        if (is_null($io)) {
            $io = $this->composerFactory->createIO();
        }

        $repositories = $this->composerFactory->createReposByPackage($io, $package);

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

        foreach ($packages as $package) {

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

        $this->dumpPackageJson($packages);
    }

    /**
     * @inheritdoc
     */
    public function hasJsonMetadata($name)
    {
        return file_exists($this->getJsonMetadataPath($name));
    }

    /**
     * @inheritdoc
     */
    public function getJsonMetadataPath($name)
    {
        return $this->packageDir . '/' . strtolower($name) . '.json';
    }

    /**
     * @inheritdoc
     */
    public function getJsonMetadata($packageName)
    {
        return json_decode(file_get_contents($this->getJsonMetadataPath($packageName)), true);
    }

    /**
     * @inheritdoc
     */
    public function loadPackages($name)
    {
        $packageData = $this->getJsonMetadata($name);
        $loader = new ArrayLoader();

        /** @var CompletePackage[] $packages */
        $packages = [];

        if (!empty($packageData['packages'][$name])) {
            foreach ($packageData['packages'][$name] as $p) {
                $p = $loader->load($p);

                if ($p instanceof AliasPackage) {
                    $p = $p->getAliasOf();
                }

                $packages[] = $p;
            }
        }

        return $packages;
    }

    /**
     * @param array|CompletePackage[] $packages
     */
    protected function dumpPackageJson(array $packages)
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->packageDir)) {
            $fs->mkdir($this->packageDir);
        }

        $uid = 0;
        $data = [];

        foreach ($packages as $package) {
            if ($package instanceof AliasPackage) {
                continue;
            }

            $distUrl = $this->getComposerDistUrl($package->getPrettyName(), $package->getSourceReference());

            $package->setDistUrl($distUrl);
            $package->setDistType('zip');
            $package->setDistReference($package->getSourceReference());

            $data[$package->getPrettyName()]['__normalized_name'] = $package->getName();
            $data[$package->getPrettyName()][$package->getPrettyVersion()] = $this->dumper->dump($package);
            $data[$package->getPrettyName()][$package->getPrettyVersion()]['uid'] = crc32($package->getName()) . ($uid++);
        }

        foreach ($data as $prettyName => $packageData) {
            $packageName = $packageData['__normalized_name'];

            unset($packageData['__normalized_name']);

            $json = new JsonFile($this->getJsonMetadataPath($packageName));

            $json->write(['packages' => [$prettyName => $packageData]]);
        }
    }

    /**
     * @inheritdoc
     */
    public function dumpPackagesJson()
    {
        $filename = $this->packageDir . '/packages.json';

        $repos = $this->registry->getRepository(Repo::class)->findAll();
        $providers = [];

        foreach ($repos as $repo) {
            if ($repo->hasPackages()) {

                foreach ($repo->getPackages() as $package) {
                    $name = $package->getName();

                    if (!isset($providers[$name]) && file_exists($this->getJsonMetadataPath($name))) {
                        $providers[$name] = [
                            'sha256' => hash_file('sha256', $this->getJsonMetadataPath($name))
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
        $repo['notify-batch'] = $this->router->generate('devliver_repository_track_downloads', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $repo['mirrors'] = [$mirror];
        $repo['providers-url'] = $this->router->generate('devliver_repository_provider_base', [], UrlGeneratorInterface::ABSOLUTE_URL) . '/%hash%/%package%.json';
        $repo['providers'] = $providers;

        $repoJson = new JsonFile($filename);
        $repoJson->write($repo);
    }

    /**
     * @inheritdoc
     */
    public function runUpdate(PackageInterface $package)
    {
        $io = $this->composerFactory->createIO();

        $this->sync($package, $io);
        $this->dumpPackagesJson();
    }

    /**
     * @param                       $package
     * @param                       $ref
     * @param                       $type
     *
     * @return string
     */
    protected function getComposerDistUrl($package, $ref, $type = 'zip')
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