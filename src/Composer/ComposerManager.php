<?php

namespace Shapecode\Devliver\Composer;

use Composer\Config;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryFactory;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\PackageInterface;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Entity\RepoInterface;
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
            $repositories = $this->registry->getRepository(Repo::class)->findAll();
            $this->config = $this->createReposConfig($repositories);
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
     * @param RepoInterface $repo
     *
     * @return Config
     */
    public function createRepoConfig(RepoInterface $repo)
    {
        return $this->createReposConfig([$repo]);
    }

    /**
     * @param array $repos
     *
     * @return Config
     */
    public function createReposConfig(array $repos)
    {
        $config = $this->configFactory->create();
        $config->merge(['repositories' => array_map(
            function (Repo $r) {
                return $r->getConfig();
            },
            $repos
        )]);

        return $config;
    }
}
