<?php

declare(strict_types=1);

namespace App\Composer;

use App\Entity\Package;
use Composer\Composer;
use Composer\Config;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Repository\RepositoryFactory;
use Composer\Repository\RepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

use function array_map;

final class ComposerManager
{
    private ManagerRegistry $registry;

    private ConfigFactory $configFactory;

    public function __construct(
        ManagerRegistry $registry,
        ConfigFactory $configFactory,
    ) {
        $this->registry      = $registry;
        $this->configFactory = $configFactory;
    }

    public function createRepository(Package $package): RepositoryInterface
    {
        $config = $this->createRepositoryConfig($package);

        return RepositoryFactory::createRepo(
            new NullIO(),
            $config,
            $package->getConfig()
        );
    }

    public function createComposer(): Composer
    {
        return Factory::create(
            new NullIO(),
            $this->getConfig()->all()
        );
    }

    public function createRepositoryByUrl(string $url, string $type = 'vcs'): RepositoryInterface
    {
        $config = $this->configFactory->create();

        return RepositoryFactory::createRepo(
            new NullIO(),
            $config,
            [
                'type' => $type,
                'url'  => $url,
            ]
        );
    }

    private function createRepositoryConfig(Package $package): Config
    {
        return $this->createRepositoriesConfig([$package]);
    }

    /**
     * @param Package[] $packages
     */
    private function createRepositoriesConfig(array $packages): Config
    {
        $config = $this->configFactory->create();
        $config->merge([
            'repositories' => array_map(
                static fn (Package $r): array => $r->getConfig(),
                $packages
            ),
        ]);

        return $config;
    }

    private function getConfig(): Config
    {
        $packages = $this->registry->getRepository(Package::class)->findAll();

        return $this->createRepositoriesConfig($packages);
    }
}
