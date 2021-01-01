<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Package;
use App\Entity\Version;
use Composer\Package\Dumper\ArrayDumper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function hash;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class PackagesDumper
{
    private ManagerRegistry $registry;

    private UrlGeneratorInterface $router;

    private RepositoryHelper $repositoryHelper;

    private TagAwareAdapterInterface $cache;

    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $router,
        RepositoryHelper $repositoryHelper,
        TagAwareAdapterInterface $cache
    ) {
        $this->registry         = $registry;
        $this->router           = $router;
        $this->repositoryHelper = $repositoryHelper;
        $this->cache            = $cache;
    }

    public function dumpPackageJson(Package $package): string
    {
        $cacheKey = 'package-' . $package->getId();
        $item     = $this->cache->getItem($cacheKey);

//        if ($item->isHit()) {
//            return $item->get();
//        }

        $data   = [];
        $dumper = new ArrayDumper();

        $repo     = $this->registry->getRepository(Version::class);
        $versions = $repo->findByPackage($package);

        foreach ($versions as $version) {
            $packageData = $dumper->dump($version->getPackageInformation());

            $packageData['uid'] = $version->getId();

            if ($package->isAbandoned()) {
                $replacement              = $package->getReplacementPackage() ?? true;
                $packageData['abandoned'] = $replacement;
            }

            $data[$version->getName()] = $packageData;
        }

        $jsonData = ['packages' => [$package->getName() => $data]];
        $json     = json_encode($jsonData, JSON_THROW_ON_ERROR);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    public function dumpPackagesJson(): string
    {
        $cacheKey = 'packages';
        $item     = $this->cache->getItem($cacheKey);

//        if ($item->isHit()) {
//            return $item->get();
//        }

        $repo     = $this->registry->getRepository(Package::class);
        $packages = $repo->findEnabled();

        $providers = [];

        foreach ($packages as $package) {
            $name = $package->getName();

            $providers[$name] = [
                'sha256' => $this->hashPackageJson($package),
            ];
        }

        $repo = [];

        $distUrl = $this->repositoryHelper->getComposerDistUrl('%package%', '%reference%', '%type%');
        $mirror  = [
            'dist-url'  => $distUrl,
            'preferred' => true,
        ];

        $repo['packages']      = [];
        $repo['notify-batch']  = $this->router->generate('app_download_track', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $repo['mirrors']       = [$mirror];
        $repo['providers-url'] = $this->router->generate('app_repository_provider_base', [], UrlGeneratorInterface::ABSOLUTE_URL) . '/%package%.json';
        $repo['providers']     = $providers;

        $json = json_encode($repo, JSON_THROW_ON_ERROR);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    public function hashPackageJson(Package $package): string
    {
        $json = $this->dumpPackageJson($package);

        return hash('sha256', $json);
    }
}
