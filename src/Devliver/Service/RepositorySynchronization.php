<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Shapecode\Devliver\Composer\ComposerFactory;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Entity\RepoInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * Class RepositorySynchronization
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class RepositorySynchronization implements RepositorySynchronizationInterface
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ComposerFactory */
    protected $composerFactory;

    /** @var PackageSynchronizationInterface */
    protected $packageSynchronization;

    /**
     * @param ManagerRegistry                 $registry
     * @param PackageSynchronizationInterface $packageSynchronization
     * @param ComposerFactory                 $composerFactory
     */
    public function __construct(ManagerRegistry $registry, PackageSynchronizationInterface $packageSynchronization, ComposerFactory $composerFactory)
    {
        $this->registry = $registry;
        $this->packageSynchronization = $packageSynchronization;
        $this->composerFactory = $composerFactory;
    }

    /**
     * @inheritdoc
     */
    public function sync(IOInterface $io = null)
    {
        if (is_null($io)) {
            $io = $this->composerFactory->createIO();
        }

        $repositories = $this->registry->getRepository(Repo::class)->findAll();

        foreach ($repositories as $repo) {
            $this->syncRepo($repo, $io);
        }

        $this->packageSynchronization->dumpPackagesJson();
    }

    /**
     * @inheritdoc
     */
    public function syncRepo(RepoInterface $repo, IOInterface $io = null)
    {
        if (is_null($io)) {
            $io = $this->composerFactory->createIO();
        }

        $repository = $this->composerFactory->createRepo($io, $repo);

        $packages = $repository->getPackages();

        if (!$packages) {
            return;
        }

        $this->packageSynchronization->save($packages, $repo);
    }

    /**
     * @inheritdoc
     */
    public function runUpdate(RepoInterface $repo)
    {
        $io = $this->composerFactory->createIO();

        $this->syncRepo($repo, $io);
        $this->packageSynchronization->dumpPackagesJson();
    }
}