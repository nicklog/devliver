<?php

namespace Shapecode\Devliver\Service;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class ConsoleHelper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class ConsoleHelper
{

    /** @var KernelInterface */
    protected $kernel;

    /**
     * @return string
     */
    public function getWorkingDirectory(): string
    {
        return $this->kernel->getProjectDir();
    }

    /**
     * @param OutputInterface $io
     * @param                 $commandStr
     * @param string          $binary
     */
    public function run(OutputInterface $io, $commandStr, $binary = 'console'): void
    {
        $pwd = $this->getWorkingDirectory();

        $projectDir = $this->kernel->getProjectDir();
        $bin = realpath($projectDir.'/bin/'.$binary);

        $executableFinder = new PhpExecutableFinder();
        $php = $executableFinder->find();

        $command = sprintf('%s %s %s', $php, $bin, $commandStr);

        $helper = new ProcessHelper();
        $process = new Process($command, $pwd);
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });
    }

    /**
     * @param OutputInterface $io
     */
    public function composerInstall(OutputInterface $io)
    {
        $this->run($io, 'install --no-scripts --no-dev --optimize-autoloader', 'composer.phar');
        $this->run($io, 'auto-scripts', 'composer.phar');
    }
}
