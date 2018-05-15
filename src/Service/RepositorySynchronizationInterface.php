<?php

namespace Shapecode\Devliver\Service;

use Composer\IO\IOInterface;
use Shapecode\Devliver\Entity\RepoInterface;

/**
 * Interface RepositorySynchronizationInterface
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
interface RepositorySynchronizationInterface
{

    /**
     * @param IOInterface|null $io
     */
    public function sync(IOInterface $io = null);

    /**
     * @param RepoInterface    $repo
     * @param IOInterface|null $io
     */
    public function syncRepo(RepoInterface $repo, IOInterface $io = null);

    /**
     * @param RepoInterface $repo
     */
    public function runUpdate(RepoInterface $repo);
}
