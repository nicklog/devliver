<?php

namespace Shapecode\Devliver\Command;

use Shapecode\Devliver\Service\GitHubRelease;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class SelfUpdateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class SelfUpdateCommand extends Command
{

    /** @var GitHubRelease */
    protected $github;

    /** @var Kernel */
    protected $kernel;

    /**
     * @param GitHubRelease $github
     * @param Kernel        $kernel
     */
    public function __construct(GitHubRelease $github, Kernel $kernel)
    {
        parent::__construct();

        $this->github = $github;
        $this->kernel = $kernel;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('devliver:self-update');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $latestRelease = $this->github->getLatestRelease();
        $asset = $latestRelease['assets'][0];

        $pwd = $this->getWorkingDirectory();
        $filename = $asset['name'];
        $filePath = $pwd . '/' . $filename;

        $io = new SymfonyStyle($input, $output);
        $io->title('Update Devliver to latest version: ' . $latestRelease['name']);

        if (!$io->confirm('Do you want update Devliver to the latest version? Do you have a backup?')) {
            $io->error('update canceled');

            return;
        }

        $this->downloadUpdateFile($io, $asset);

        if (!file_exists($filePath)) {
            $io->error('Couldn\'t find update file. Abort!');

            return;
        }

        $this->removeSources($io, $asset);
        $this->unzip($io, $asset);
        $this->composerInstall($io, $asset);
        $this->updateDatabase($io, $asset);
        $this->removeUpdateFile($io, $asset);

        $io->success('Devliver updated to latest version: ' . $latestRelease['name']);

    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function downloadUpdateFile(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $downloadUrl = $asset['browser_download_url'];
        $filename = $asset['name'];
        $filePath = $pwd . '/' . $filename;

        $io->section('download update file');
        $io->text('download in progress... please wait.');
        copy($downloadUrl, $filePath);
        $io->text('download finished');
    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function removeSources(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $fs = new Filesystem();

        $list = [
            $pwd . '/bin',
            $pwd . '/config',
            $pwd . '/public',
            $pwd . '/src',
            $pwd . '/templates',
            $pwd . '/translations',
            $pwd . '/var/cache',
            $pwd . '/vendor',
        ];

        $io->section('remove old source');

        foreach ($list as $item) {
            $io->text('remove: ' . $item);
        }

        $fs->remove($list);

    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function unzip(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $filename = $asset['name'];
        $filePath = $pwd . '/' . $filename;

        $io->section('unzip update file');

        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $zip->extractTo($pwd);
            $zip->close();
        } else {
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function composerInstall(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $helper = $this->getHelper('process');

        $io->section('composer install');

        $projectDir = $this->kernel->getProjectDir();
        $bin = $projectDir . '/bin/composer';

        $executableFinder = new PhpExecutableFinder();
        $php = $executableFinder->find();

        $command = sprintf('%s %s install --no-dev --optimize-autoloader', $php, $bin);

        $process = new Process($command, $pwd);
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });
    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function updateDatabase(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $helper = $this->getHelper('process');

        $io->section('update database');

        $projectDir = $this->kernel->getProjectDir();
        $bin = $projectDir . '/bin/console';

        $executableFinder = new PhpExecutableFinder();
        $php = $executableFinder->find();

        $command = sprintf('%s %s doctrine:schema:update --dump-sql', $php, $bin);

        $process = new Process($command, $pwd);
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });
    }

    /**
     * @param SymfonyStyle $io
     * @param              $asset
     */
    protected function removeUpdateFile(SymfonyStyle $io, $asset)
    {
        $pwd = $this->getWorkingDirectory();
        $filename = $asset['name'];
        $filePath = $pwd . '/' . $filename;
        $fs = new Filesystem();

        $io->section('remove update file');
        $fs->remove($filePath);
        $io->text('update file removed');
    }

    /**
     * @return string
     */
    protected function getWorkingDirectory()
    {
        return $this->kernel->getProjectDir();
    }

}
