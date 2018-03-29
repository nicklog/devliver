<?php

namespace Shapecode\Devliver\Command;

use Shapecode\Devliver\Service\GitHubRelease;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    /**
     * @param GitHubRelease $github
     */
    public function __construct(GitHubRelease $github)
    {
        parent::__construct();

        $this->github = $github;
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
        $helper = $this->getHelper('process');

        $latestRelease = $this->github->getLatestRelease();
        $asset = $latestRelease['assets'][0];

        $downloadUrl = $asset['browser_download_url'];
        $filename = $asset['name'];

        $io = new SymfonyStyle($input, $output);
        $io->title('Update Devliver to latest version: ' . $latestRelease['name']);

        if (!$io->confirm('Do you want update Devliver to the latest version? Do you have a backup?')) {
            $io->error('update canceled');

            return;
        }

        $process = new Process(['wget', '-N', $downloadUrl], $pwd);
        $io->section('download update file...');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });

        if (!file_exists($pwd . '/' . $filename.'2')) {
            $io->error('Couldn\'t find update file. Abort!');

            return;
        }

        $process = new Process('rm -rf bin/ config/ public/ src/ templates/ translations/ var/cache/ vendor/', $pwd);
        $io->section('remove old source');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
//            $io->write($data);
        });

        $process = new Process('unzip -o ' . $filename, $pwd);
        $io->section('unzip new source');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
//            $io->write($data);
        });

        $process = new Process('php bin/composer install --no-dev --optimize-autoloader', $pwd);
        $io->section('composer install');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });

        $process = new Process('php bin/console doctrine:schema:update --dump-sql', $pwd);
        $io->section('update database');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });

        $process = new Process('rm ' . $filename, $pwd);
        $io->section('remove update file');
        $io->text($process->getCommandLine());
        $helper->run($io, $process, null, function ($type, $data) use ($io) {
            $io->write($data);
        });

        $io->success('Devliver updated to latest version: ' . $latestRelease['name']);

    }

}
