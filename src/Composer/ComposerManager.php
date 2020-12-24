<?php

declare(strict_types=1);

namespace App\Composer;

use App\Entity\Package;
use Composer\Config;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryFactory;
use Composer\Repository\RepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

use function array_map;

class ComposerManager
{
    protected ManagerRegistry $registry;

    protected Config $config;

    protected ConfigFactory $configFactory;

    public function __construct(ManagerRegistry $registry, ConfigFactory $configFactory)
    {
        $this->registry      = $registry;
        $this->configFactory = $configFactory;
    }

    public function createIO(): BufferIO
    {
        $io = new BufferIO('', OutputInterface::VERBOSITY_VERBOSE);
        $io->loadConfiguration($this->getConfig());

        return $io;
    }

    public function getConfig(): Config
    {
        if ($this->config === null) {
            $packages     = $this->registry->getRepository(Package::class)->findAll();
            $this->config = $this->createRepositoriesConfig($packages);
        }

        return $this->config;
    }

    /**
     * @deprecated
     */
    public function create(): Config
    {
        return $this->getConfig();
    }

    public function createRepository(Package $package, ?IOInterface $io = null): RepositoryInterface
    {
        if ($io === null) {
            $io = $this->createIO();
        }

        $config = $this->createRepositoryConfig($package);

        return RepositoryFactory::createRepo($io, $config, $package->getConfig());
    }

    /**
     * @return RepositoryInterface|mixed
     */
    public function createRepositoryByUrl(string $url, string $type = 'vcs', ?IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->createIO();
        }

        $config = $this->configFactory->create();

        return RepositoryFactory::createRepo($io, $config, [
            'type' => $type,
            'url'  => $url,
        ]);
    }

    public function createRepositoryConfig(Package $package): Config
    {
        return $this->createRepositoriesConfig([$package]);
    }

    /**
     * @param Package[] $packages
     */
    public function createRepositoriesConfig(array $packages): Config
    {
        $config = $this->configFactory->create();
        $config->merge([
            'repositories' => array_map(
                static function (Package $r) {
                    return $r->getConfig();
                },
                $packages
            ),
        ]);

        return $config;
    }
}
