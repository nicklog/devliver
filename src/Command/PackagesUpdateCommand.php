<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Package;
use App\Service\PackageSynchronization;
use Composer\IO\ConsoleIO;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

use function ini_set;
use function set_time_limit;

class PackagesUpdateCommand extends Command
{
    protected ManagerRegistry $registry;

    protected PackageSynchronization $packageSynchronization;

    public function __construct(
        ManagerRegistry $registry,
        PackageSynchronization $packageSynchronization
    ) {
        parent::__construct();

        $this->registry               = $registry;
        $this->packageSynchronization = $packageSynchronization;
    }

    protected function configure(): void
    {
        $this
            ->setName('devliver:packages:update')
            ->setHelp('Syncs all Packages')
            ->addArgument('package', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $store   = new SemaphoreStore();
        $factory = new LockFactory($store);

        $lock = $factory->createLock('devliver_packages_update');

        // another job is still active
        if (! $lock->acquire()) {
            $output->writeln('Aborting, lock file is present.');

            return 1;
        }

        $package = $input->getArgument('package');

        if ($package !== null) {
            $repo    = $this->registry->getRepository(Package::class);
            $package = $repo->findOneBy([
                'name' => $package,
            ]);

            // another job is still active
            if ($package === null) {
                $output->writeln('Aborting, package not found.');

                return 1;
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

        return 0;
    }

    protected function createIO(InputInterface $input, OutputInterface $output): ConsoleIO
    {
        return new ConsoleIO($input, $output, $this->getHelperSet());
    }
}
