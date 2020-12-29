<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UpdateQueueRepository;
use App\Service\PackageSynchronization;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PackagesQueueCommand extends Command
{
    protected ManagerRegistry $registry;

    protected PackageSynchronization $packageSynchronization;

    private UpdateQueueRepository $updateQueueRepository;

    public function __construct(
        ManagerRegistry $registry,
        PackageSynchronization $packageSynchronization,
        UpdateQueueRepository $updateQueueRepository,
    ) {
        parent::__construct();

        $this->registry               = $registry;
        $this->packageSynchronization = $packageSynchronization;
        $this->updateQueueRepository  = $updateQueueRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:queue:execute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->registry->getManager();

        $queues = $this->updateQueueRepository->findUnlocked();

        foreach ($queues as $queue) {
            $queue->setLockedAt(new DateTime());
            $em->persist($queue);
        }

        $em->flush();

        foreach ($queues as $queue) {
            $this->packageSynchronization->sync($queue->getPackage());

            $em->remove($queue);
            $em->flush();
        }

        return 0;
    }
}
