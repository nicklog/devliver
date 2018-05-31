<?php

namespace Shapecode\Devliver\Composer;

use Composer\Config;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryFactory;
use Composer\Repository\RepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\PackageInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ComposerManager
 *
 * @package Shapecode\Devliver\Composer
 * @author  Nikita Loges
 */
class ComposerManager
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var Config */
    protected $config;

    /** @var ConfigFactory */
    protected $configFactory;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigFactory   $configFactory
     */
    public function __construct(ManagerRegistry $registry, ConfigFactory $configFactory)
    {
        $this->registry = $registry;
        $this->configFactory = $configFactory;
    }

    /**
     * @return BufferIO
     */
    public function createIO()
    {
        $io = new BufferIO('', OutputInterface::VERBOSITY_VERBOSE);
        $io->loadConfiguration($this->getConfig());

        return $io;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $packages = $this->registry->getRepository(Package::class)->findAll();
            $this->config = $this->createRepositoriesConfig($packages);
        }

        return $this->config;
    }

    /**
     * @return Config
     * @deprecated
     */
    public function create()
    {
        return $this->getConfig();
    }

    /**
     * @param PackageInterface $package
     * @param IOInterface|null $io
     *
     * @return RepositoryInterface
     */
    public function createRepository(PackageInterface $package, IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->createIO();
        }

        $config = $this->createRepositoryConfig($package);

        return RepositoryFactory::createRepo($io, $config, $package->getConfig());
    }

    /**
     * @param string           $url
     * @param string           $type
     * @param IOInterface|null $io
     *
     * @return RepositoryInterface|mixed
     */
    public function createRepositoryByUrl(string $url, string $type = 'vcs', IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->createIO();
        }

        $config = $this->configFactory->create();

        return RepositoryFactory::createRepo($io, $config, [
            'type' => $type,
            'url'  => $url
        ]);
    }

    /**
     * @param PackageInterface $package
     *
     * @return Config
     */
    public function createRepositoryConfig(PackageInterface $package)
    {
        return $this->createRepositoriesConfig([$package]);
    }

    /**
     * @param PackageInterface[] $packages
     *
     * @return Config
     */
    public function createRepositoriesConfig(array $packages)
    {
        $config = $this->configFactory->create();
        $config->merge(['repositories' => array_map(
            function (PackageInterface $r) {
                return $r->getConfig();
            },
            $packages
        )]);

        return $config;
    }
}
