<?php

namespace Shapecode\Devliver\Command;

use Composer\IO\ConsoleIO;
use Shapecode\Bundle\CronBundle\Annotation\CronJob;
use Shapecode\Devliver\Service\RepositorySynchronizationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Class PackagesUpdateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 *
 * @CronJob("*\/5 * * * *", arguments="-q")
 */
class PackagesUpdateCommand extends Command
{

    /** @var RepositorySynchronizationInterface */
    protected $repositorySynchronization;

    /**
     * @param RepositorySynchronizationInterface $repositorySynchronization
     */
    public function __construct(RepositorySynchronizationInterface $repositorySynchronization)
    {
        parent::__construct();

        $this->repositorySynchronization = $repositorySynchronization;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('devliver:packages:update');
        $this->setHelp('Syncs all Packages');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = new SemaphoreStore();
        $factory = new Factory($store);
        $lock = $factory->createLock('devliver_packages_update');

        // another job is still active
        if (!$lock->acquire()) {
            $output->writeln('Aborting, lock file is present.');

            return;
        }

        ini_set('memory_limit', -1);
        set_time_limit(0);

        $this->sync($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function sync(InputInterface $input, OutputInterface $output)
    {
        $io = $this->createIO($input, $output);

        $this->repositorySynchronization->sync($io);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ConsoleIO
     */
    protected function createIO(InputInterface $input, OutputInterface $output)
    {
        return new ConsoleIO($input, $output, $this->getHelperSet());
    }
}
