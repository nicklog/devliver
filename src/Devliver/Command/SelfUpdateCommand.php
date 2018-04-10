<?php

namespace Shapecode\Devliver\Command;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
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
        $lastTag = $this->github->getTagByTagName($latestRelease['tag_name']);

        $pwd = $this->getWorkingDirectory();
        $filename = $lastTag['name'] . '.zip';
        $filePath = $pwd . '/' . $filename;

        $io = new SymfonyStyle($input, $output);
        $io->title('Update Devliver to latest version: ' . $latestRelease['name']);

        if (!$io->confirm('Do you want update Devliver to the latest version? Do you have a backup?')) {
            $io->error('update canceled');

            return;
        }

        $this->downloadUpdateFile($io, $filePath, $lastTag['zipball_url']);

        if (!file_exists($filePath)) {
            $io->error('Couldn\'t find update file. Abort!');

            return;
        }

        $this->removeSources($io);
        $this->unzip($io, $filePath, $lastTag);
        $this->composerInstall($io);
        $this->updateDatabase($io);
        $this->removeUpdateFile($io, $filePath);

        $io->success('Devliver updated to latest version: ' . $latestRelease['name']);

    }

    /**
     * @param SymfonyStyle $io
     * @param              $zipball
     * @param              $url
     */
    protected function downloadUpdateFile(SymfonyStyle $io, $zipball, $url)
    {
        $io->section('download update file');
        $io->text('download in progress... please wait.');
        if (!file_exists($zipball)) {
            $this->downloadZipBall($zipball, $url);
        }
        $io->text('download finished');
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function removeSources(SymfonyStyle $io)
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
        ];

        $io->section('remove old source');

        foreach ($list as $item) {
            $io->text('remove: ' . $item);
        }

        $fs->remove($list);

    }

    /**
     * @param SymfonyStyle $io
     * @param              $zipball
     * @param              $lastTag
     */
    protected function unzip(SymfonyStyle $io, $zipball, $lastTag)
    {
        $pwd = $this->getWorkingDirectory();

        $io->section('unzip update file');

        $commit = substr($lastTag['commit']['sha'], 0, 7);
        $zipDirName = 'shapecode-devliver-' . $commit;

        $fs = new Filesystem();
        $zip = new \ZipArchive();
        if ($zip->open($zipball) === true) {
            $zip->extractTo($pwd);
            $zip->close();

            $fs->mirror($pwd . '/' . $zipDirName . '/', $pwd, null, [
                'override' => true
            ]);
            $fs->remove($pwd . '/' . $zipDirName);
        }
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function composerInstall(SymfonyStyle $io)
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
     */
    protected function updateDatabase(SymfonyStyle $io)
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
     * @param              $filePath
     */
    protected function removeUpdateFile(SymfonyStyle $io, $filePath)
    {
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

    /**
     * @param $dest
     * @param $source
     */
    protected function downloadZipBall($dest, $source)
    {
        $client = HttpClientDiscovery::find();
        $messageFactory = MessageFactoryDiscovery::find();
        $findLocation = $client->sendRequest(
            $messageFactory->createRequest('GET', $source)
        );

        $location = $findLocation->getHeader('Location')[0];

        $download = $client->sendRequest(
            $messageFactory->createRequest('GET', $location)
        );

        file_put_contents($dest, $download->getBody());
    }

}
