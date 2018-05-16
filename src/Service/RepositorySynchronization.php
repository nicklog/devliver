<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Shapecode\Devliver\Composer\ComposerManager;
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

    /** @var ComposerManager */
    protected $composerManager;

    /** @var PackageSynchronizationInterface */
    protected $packageSynchronization;

    /**
     * @param ManagerRegistry                 $registry
     * @param PackageSynchronizationInterface $packageSynchronization
     * @param ComposerManager                 $composerManager
     */
    public function __construct(ManagerRegistry $registry, PackageSynchronizationInterface $packageSynchronization, ComposerManager $composerManager)
    {
        $this->registry = $registry;
        $this->packageSynchronization = $packageSynchronization;
        $this->composerManager = $composerManager;
    }

    /**
     * @inheritdoc
     */
    public function sync(IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->composerManager->createIO();
        }

        $repositories = $this->registry->getRepository(Repo::class)->findAll();

        foreach ($repositories as $repo) {
            $this->syncRepo($repo, $io);
        }
    }

    /**
     * @inheritdoc
     */
    public function syncRepo(RepoInterface $repo, IOInterface $io = null)
    {
        if ($io === null) {
            $io = $this->composerManager->createIO();
        }

        $repository = $this->composerManager->createRepo($io, $repo);

        $packages = $repository->getPackages();

        if (!$packages) {
            return;
        }

        $this->packageSynchronization->save($packages, $repo);
    }
}