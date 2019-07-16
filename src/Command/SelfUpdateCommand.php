<?php

namespace Shapecode\Devliver\Command;

use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Shapecode\Devliver\Service\ConsoleHelper;
use Shapecode\Devliver\Service\GitHubRelease;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class SelfUpdateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 */
class SelfUpdateCommand extends Command
{

    /** @var GitHubRelease */
    protected $github;

    /** @var ConsoleHelper */
    protected $consoleHelper;

    /**
     * @param GitHubRelease $github
     * @param ConsoleHelper $consoleHelper
     */
    public function __construct(GitHubRelease $github, ConsoleHelper $consoleHelper)
    {
        parent::__construct();

        $this->github = $github;
        $this->consoleHelper = $consoleHelper;
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
        $lastTag = $this->github->getLastTag();

        $pwd = $this->consoleHelper->getWorkingDirectory();
        $filename = $lastTag['name'].'.zip';
        $filePath = $pwd.'/'.$filename;

        $io = new SymfonyStyle($input, $output);
        $io->title('Update Devliver to latest version: '.$lastTag['name']);

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
        $this->removeUpdateFile($io, $filePath);

        $this->github->setVersionData($lastTag);

        $io->success('Devliver updated to latest version: '.$lastTag['name']);

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
        $pwd = $this->consoleHelper->getWorkingDirectory();
        $fs = new Filesystem();

        $list = [
            $pwd.'/bin',
            $pwd.'/config',
            $pwd.'/public',
            $pwd.'/src',
            $pwd.'/templates',
            $pwd.'/translations',
            $pwd.'/var/cache',
        ];

        $io->section('remove old source');

        foreach ($list as $item) {
            $io->text('remove: '.$item);
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
        $pwd = $this->consoleHelper->getWorkingDirectory();

        $io->section('unzip update file');

        $commit = substr($lastTag['commit']['sha'], 0, 7);
        $zipDirName = 'shapecode-devliver-'.$commit;

        $fs = new Filesystem();
        $zip = new \ZipArchive();
        if ($zip->open($zipball) === true) {
            $zip->extractTo($pwd);
            $zip->close();

            $fs->mirror($pwd.'/'.$zipDirName.'/', $pwd, null, [
                'override' => true,
            ]);
            $fs->remove($pwd.'/'.$zipDirName);
        }
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function composerInstall(SymfonyStyle $io)
    {
        $io->section('composer install');

        $this->consoleHelper->composerInstall($io);
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
     * @param $dest
     * @param $source
     */
    protected function downloadZipBall($dest, $source)
    {
        $redirectPlugin = new RedirectPlugin();
        $client = HttpClientDiscovery::find();

        $pluginClient = new PluginClient(
            $client,
            [$redirectPlugin]
        );

        $messageFactory = MessageFactoryDiscovery::find();
        $download = $pluginClient->sendRequest(
            $messageFactory->createRequest('GET', $source)
        );

        file_put_contents($dest, $download->getBody());
    }

}
