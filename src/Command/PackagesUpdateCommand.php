<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Package;
use App\Service\PackageSynchronization;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

use function ini_set;
use function set_time_limit;

final class PackagesUpdateCommand extends Command
{
    private ManagerRegistry $registry;

    private PackageSynchronization $packageSynchronization;

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
            ->setName('app:packages:update')
            ->setHelp('Syncs all or a specific package')
            ->addArgument('package', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $store   = new SemaphoreStore();
        $factory = new LockFactory($store);

        $lock = $factory->createLock('packages_update');

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

        ini_set('memory_limit', '-1');
        set_time_limit(600);

        if ($package !== null) {
            $this->packageSynchronization->sync($package);
        } else {
            $this->packageSynchronization->syncAll();
        }

        return 0;
    }
}
