<?php

namespace Shapecode\Devliver\Command;

use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Repository\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class MigrateCommand extends ContainerAwareCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('devliver:migrate');
        $this->setHelp('Migrate Data');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        /** @var PackageRepository $packageRepo */
        $packageRepo = $em->getRepository(Package::class);

        /** @var Package[] $packages */
        $packages = $packageRepo->findWithRepos();

        foreach ($packages as $package) {
            $repo = $package->getRepo();

            $package->setUrl($repo->getUrl());
            $package->setType($repo->getType());
            $package->setCreator($repo->getCreator());

            $package->setRepo(null);

            $em->remove($repo);
            $em->persist($package);
        }

        $em->flush();
    }
}
