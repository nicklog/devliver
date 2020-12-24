<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Package;
use App\Entity\User;
use App\Entity\Version;
use App\Repository\PackageRepository;
use App\Repository\VersionRepository;
use Composer\Package\Dumper\ArrayDumper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function assert;
use function json_encode;
use function sha1;

class PackagesDumper
{
    protected ManagerRegistry $registry;

    protected UrlGeneratorInterface $router;

    protected RepositoryHelper $repositoryHelper;

    protected TagAwareAdapterInterface $cache;

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

    public function dumpPackageJson(User $user, Package $package): string
    {
        $cacheKey = 'user-package-json-' . $package->getId();
        $item     = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $item->tag('packages-package-' . $package->getId());

        $data   = [];
        $dumper = new ArrayDumper();

        $repo = $this->registry->getRepository(Version::class);
        assert($repo instanceof VersionRepository);
        $versions = $repo->findAccessibleForUser($user, $package);

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
        $json     = json_encode($jsonData);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    public function dumpPackagesJson(User $user): string
    {
        $cacheKey = 'user-packages-json-' . $user->getId();
        $item     = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $item->tag('packages.json');
        $item->tag('packages-user-' . $user->getId());

        $repo = $this->registry->getRepository(Package::class);
        assert($repo instanceof PackageRepository);
        $packages = $repo->findAccessibleForUser($user);

        $providers = [];

        foreach ($packages as $package) {
            $name = $package->getName();

            $item->tag('packages-package-' . $package->getId());

            $providers[$name] = [
                'sha256' => $this->hashPackageJson($user, $package),
            ];
        }

        $repo = [];

        $distUrl = $this->repositoryHelper->getComposerDistUrl('%package%', '%reference%', '%type%');
        $mirror  = [
            'dist-url'  => $distUrl,
            'preferred' => true,
        ];

        $repo['packages']      = [];
        $repo['notify-batch']  = $this->router->generate('devliver_download_track', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $repo['mirrors']       = [$mirror];
        $repo['providers-url'] = $this->router->generate('devliver_repository_provider_base', [], UrlGeneratorInterface::ABSOLUTE_URL) . '/%package%.json';
        $repo['providers']     = $providers;

        $json = json_encode($repo);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    public function hashPackageJson(User $user, Package $package): string
    {
        $json = $this->dumpPackageJson($user, $package);

        return sha1($json);
    }
}
