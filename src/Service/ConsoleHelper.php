<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function realpath;
use function sprintf;

class ConsoleHelper
{
    protected KernelInterface $kernel;

    public function getWorkingDirectory(): string
    {
        return $this->kernel->getProjectDir();
    }

    public function run(
        OutputInterface $io,
        string $commandStr,
        string $binary = 'console'
    ): void {
        $pwd = $this->getWorkingDirectory();

        $projectDir = $this->kernel->getProjectDir();
        $bin        = realpath($projectDir . '/bin/' . $binary);

        $executableFinder = new PhpExecutableFinder();
        $php              = $executableFinder->find();

        $command = sprintf('%s %s %s', $php, $bin, $commandStr);

        $helper  = new ProcessHelper();
        $process = new Process($command, $pwd);
        $helper->run($io, $process, null, static function ($type, $data) use ($io): void {
            $io->write($data);
        });
    }

    public function composerInstall(OutputInterface $io): void
    {
        $this->run($io, 'install --no-scripts --no-dev --optimize-autoloader', 'composer.phar');
        $this->run($io, 'auto-scripts', 'composer.phar');
    }
}
