<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\UpdateQueue;
use App\Service\PackageSynchronization;
use Composer\IO\ConsoleIO;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackagesQueueCommand extends Command
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
        $this->setName('devliver:queue:execute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em        = $this->registry->getManager();
        $queueRepo = $em->getRepository(UpdateQueue::class);

        $queues = $queueRepo->findUnlocked();

        foreach ($queues as $queue) {
            $queue->setLockedAt(new DateTime());
            $em->persist($queue);
        }

        $em->flush();

        $io = $this->createIO($input, $output);

        foreach ($queues as $queue) {
            $this->packageSynchronization->sync($queue->getPackage(), $io);

            $em->remove($queue);
            $em->flush();
        }

        return 0;
    }

    protected function createIO(InputInterface $input, OutputInterface $output): ConsoleIO
    {
        return new ConsoleIO($input, $output, $this->getHelperSet());
    }
}
