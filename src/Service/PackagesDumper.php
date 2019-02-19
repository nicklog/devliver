<?php

namespace Shapecode\Devliver\Service;

use Composer\Package\Dumper\ArrayDumper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Entity\Version;
use Shapecode\Devliver\Repository\PackageRepository;
use Shapecode\Devliver\Repository\VersionRepository;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tenolo\Utilities\Utils\CryptUtil;

/**
 * Class PackagesDumper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class PackagesDumper
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var RepositoryHelper */
    protected $repositoryHelper;

    /** @var TagAwareAdapterInterface */
    protected $cache;

    /**
     * @param ManagerRegistry          $registry
     * @param UrlGeneratorInterface    $router
     * @param RepositoryHelper         $repositoryHelper
     * @param TagAwareAdapterInterface $cache
     */
    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $router,
        RepositoryHelper $repositoryHelper,
        TagAwareAdapterInterface $cache
    ) {
        $this->registry = $registry;
        $this->router = $router;
        $this->repositoryHelper = $repositoryHelper;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function dumpPackageJson(User $user, Package $package): string
    {
        $cacheKey = 'user-package-json-'.$package->getId();
        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $item->tag('packages-package-'.$package->getId());

        $data = [];
        $dumper = new ArrayDumper();

        /** @var VersionRepository $repo */
        $repo = $this->registry->getRepository(Version::class);
        $versions = $repo->findAccessibleForUser($user, $package);

        foreach ($versions as $version) {
            $packageData = $dumper->dump($version->getPackageInformation());

            $packageData['uid'] = $version->getId();

            if ($package->isAbandoned()) {
                $replacement = $package->getReplacementPackage() ?? true;
                $packageData['abandoned'] = $replacement;
            }

            $data[$version->getName()] = $packageData;
        }

        $jsonData = ['packages' => [$package->getName() => $data]];
        $json = json_encode($jsonData);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function dumpPackagesJson(User $user): string
    {
        $cacheKey = 'user-packages-json-'.$user->getId();
        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $item->tag('packages.json');
        $item->tag('packages-user-'.$user->getId());

        /** @var PackageRepository $repo */
        $repo = $this->registry->getRepository(Package::class);
        $packages = $repo->findAccessibleForUser($user);

        $providers = [];

        foreach ($packages as $package) {
            $name = $package->getName();

            $item->tag('packages-package-'.$package->getId());

            $providers[$name] = [
                'sha256' => $this->hashPackageJson($user, $package),
            ];
        }

        $repo = [];

        $distUrl = $this->repositoryHelper->getComposerDistUrl('%package%', '%reference%', '%type%');
        $mirror = [
            'dist-url'  => $distUrl,
            'preferred' => true,
        ];

        $repo['packages'] = [];
        $repo['notify-batch'] = $this->router->generate('devliver_download_track', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $repo['mirrors'] = [$mirror];
        $repo['providers-url'] = $this->router->generate('devliver_repository_provider_base', [], UrlGeneratorInterface::ABSOLUTE_URL).'/%package%.json';
        $repo['providers'] = $providers;

        $json = json_encode($repo);

        $item->set($json);
        $item->expiresAfter(96400);

        $this->cache->save($item);

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function hashPackageJson(User $user, Package $package): string
    {
        $json = $this->dumpPackageJson($user, $package);

        return CryptUtil::sha256($json);
    }
}
