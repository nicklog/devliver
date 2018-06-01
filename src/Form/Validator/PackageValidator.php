<?php

namespace Shapecode\Devliver\Form\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Composer\ComposerManager;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Service\RepositoryHelper;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PackageValidator
 *
 * @package Shapecode\Devliver\Form\Validator
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class PackageValidator
{

    /** @var ComposerManager */
    protected $composerManager;

    /** @var RepositoryHelper */
    protected $repositoryHelper;

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ComposerManager  $composerManager
     * @param RepositoryHelper $repositoryHelper
     * @param ManagerRegistry  $registry
     */
    public function __construct(ComposerManager $composerManager, RepositoryHelper $repositoryHelper, ManagerRegistry $registry)
    {
        $this->composerManager = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
        $this->registry = $registry;
    }

    /**
     * @param                           $object
     * @param ExecutionContextInterface $context
     * @param                           $payload
     */
    public function validateRepository($object, ExecutionContextInterface $context, $payload)
    {
        $url = $object['url'];
        $type = $object['type'];

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info = $this->repositoryHelper->getComposerInformation($repository);

        if ($info === null) {
            $context->addViolation('Url is invalid');
        }
    }

    /**
     * @param                           $object
     * @param ExecutionContextInterface $context
     * @param                           $payload
     */
    public function validateAddName($object, ExecutionContextInterface $context, $payload)
    {
        $url = $object['url'];
        $type = $object['type'];

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info = $this->repositoryHelper->getComposerInformation($repository);

        if (!isset($info['name'])) {
            $context->addViolation('composer.json does not include a valid name.');

            return;
        }

        $package = $this->registry->getRepository(Package::class)->findOneByName($info['name']);

        if ($package !== null) {
            $context->addViolation('Package name already exists.', [
                'name' => $package->getName()
            ]);
        }
    }

    /**
     * @param                           $object
     * @param ExecutionContextInterface $context
     * @param                           $payload
     */
    public function validateEditName($object, ExecutionContextInterface $context, $payload)
    {
        $url = $object['url'];
        $type = $object['type'];

        /** @var Package $package */
        $package = $payload['package'];

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info = $this->repositoryHelper->getComposerInformation($repository);

        if (!isset($info['name'])) {
            $context->addViolation('composer.json does not include a valid name.');

            return;
        }

        if ($package->getName() !== $info['name']) {
            $context->addViolation('Package name is not accessible with this url.', [
                'name' => $package->getName()
            ]);

            return;
        }
    }
}
