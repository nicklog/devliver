<?php

namespace Shapecode\Devliver\Composer;

use Composer\Config;
use Composer\Factory;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryFactory;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Entity\RepoInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConfigFactory
 *
 * @package Shapecode\Devliver\Composer
 * @author  Nikita Loges
 */
class ComposerFactory
{

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return Config
     */
    public function create()
    {
        $repositories = $this->registry->getRepository(Repo::class)->findAll();

        unset(Config::$defaultRepositories['packagist.org']);

        $config = Factory::createConfig();
        $config->merge(['repositories' => array_map(
            function (Repo $r) {
                return $r->getConfig();
            },
            $repositories
        )]);

        return $config;
    }

    /**
     * @param RepoInterface $repo
     *
     * @return Config
     */
    public function createRepoConfig(RepoInterface $repo)
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $config = Factory::createConfig();
        $config->merge(['repositories' => [$repo->getConfig()]]);

        return $config;
    }

    /**
     * @param array $repos
     *
     * @return Config
     */
    public function createReposConfig(array $repos)
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $config = Factory::createConfig();
        $config->merge(['repositories' => array_map(
            function (Repo $r) {
                return $r->getConfig();
            },
            $repos
        )]);

        return $config;
    }

    /**
     * @param IOInterface   $io
     * @param RepoInterface $repo
     *
     * @return \Composer\Repository\RepositoryInterface
     */
    public function createRepo(IOInterface $io, RepoInterface $repo)
    {
        $config = $this->createRepoConfig($repo);
        $repository = RepositoryFactory::createRepo($io, $config, $repo->getConfig());

        return $repository;
    }

    /**
     * @param IOInterface      $io
     * @param PackageInterface $package
     *
     * @return \Composer\Repository\RepositoryInterface[]
     */
    public function createReposByPackage(IOInterface $io, PackageInterface $package)
    {
        $config = $this->createReposConfig($package->getRepos()->toArray());
        $repository = RepositoryFactory::defaultRepos($io, $config);

        return $repository;
    }

    /**
     * @return BufferIO
     */
    public function createIO()
    {
        $io = new BufferIO('', OutputInterface::VERBOSITY_VERBOSE);
        $io->loadConfiguration($this->create());

        return $io;
    }
}
