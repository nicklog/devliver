<?php

namespace Shapecode\Devliver\Command;

use Composer\IO\ConsoleIO;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Service\PackageSynchronizationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Class PackagesUpdateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 */
class PackagesUpdateCommand extends Command
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var PackageSynchronizationInterface */
    protected $packageSynchronization;

    /**
     * @param ManagerRegistry                 $registry
     * @param PackageSynchronizationInterface $packageSynchronization
     */
    public function __construct(ManagerRegistry $registry, PackageSynchronizationInterface $packageSynchronization)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->packageSynchronization = $packageSynchronization;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('devliver:packages:update');
        $this->setHelp('Syncs all Packages');

        $this->addArgument('package', InputArgument::OPTIONAL);
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

        $package = $input->getArgument('package');

        if ($package !== null) {
            $repo = $this->registry->getRepository(Package::class);
            $package = $repo->findOneBy([
                'name' => $package
            ]);

            // another job is still active
            if ($package === null) {
                $output->writeln('Aborting, package not found.');

                return;
            }
        }

        ini_set('memory_limit', -1);
        set_time_limit(600);

        $io = $this->createIO($input, $output);

        if ($package) {
            $this->packageSynchronization->sync($package, $io);
        } else {
            $this->packageSynchronization->syncAll($io);
        }
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
