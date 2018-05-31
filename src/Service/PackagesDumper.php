<?php

namespace Shapecode\Devliver\Service;

use Composer\Package\Dumper\ArrayDumper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Repository\PackageRepository;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PackagesDumper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class PackagesDumper implements PackagesDumperInterface
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var TagAwareAdapterInterface */
    protected $cache;

    /**
     * @param ManagerRegistry          $registry
     * @param UrlGeneratorInterface    $router
     * @param TagAwareAdapterInterface $cache
     */
    public function __construct(ManagerRegistry $registry, UrlGeneratorInterface $router, TagAwareAdapterInterface $cache)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function dumpPackageJson(User $user, PackageInterface $package): string
    {
        $data = [];
        $dumper = new ArrayDumper();

        foreach ($package->getVersions() as $version) {
            $packageData = $dumper->dump($version->getPackageInformation());

            $data[$version->getName()] = $packageData;
            $data[$version->getName()]['uid'] = $version->getId();
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

        /** @var PackageRepository $repo */
        $repo = $this->registry->getRepository(Package::class);
        $packages = $repo->findDumps();

        $providers = [];

        foreach ($packages as $package) {
            $name = $package->getName();

            $item->tag('packages-package-' . $package->getId());

            $providers[$name] = [
                'sha256' => null
            ];
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
