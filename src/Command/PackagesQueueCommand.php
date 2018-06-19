<?php

namespace Shapecode\Devliver\Command;

use Composer\IO\ConsoleIO;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Bundle\CronBundle\Annotation\CronJob;
use Shapecode\Devliver\Entity\UpdateQueue;
use Shapecode\Devliver\Service\PackageSynchronizationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PackagesUpdateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 *
 * @CronJob("* * * * *", arguments="-q")
 */
class PackagesQueueCommand extends Command
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
        $this->setName('devliver:queue:execute');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->registry->getManager();
        $queueRepo = $em->getRepository(UpdateQueue::class);

        $queues = $queueRepo->findUnlocked();

        foreach ($queues as $queue) {
            $queue->setLockedAt(new \DateTime());
            $em->persist($queue);
        }

        $em->flush();

        $io = $this->createIO($input, $output);

        foreach ($queues as $queue) {
            $this->packageSynchronization->sync($queue->getPackage(), $io);

            $em->remove($queue);
            $em->flush();
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
