<?php

declare(strict_types=1);

namespace App\Form\Validator;

use App\Composer\ComposerManager;
use App\Entity\Package;
use App\Service\RepositoryHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use function assert;

class PackageValidator
{
    protected ComposerManager $composerManager;

    protected RepositoryHelper $repositoryHelper;

    protected ManagerRegistry $registry;

    public function __construct(
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper,
        ManagerRegistry $registry
    ) {
        $this->composerManager  = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
        $this->registry         = $registry;
    }

    /**
     * @param mixed[] $object
     * @param mixed[] $payload
     */
    public function validateRepository(array $object, ExecutionContextInterface $context, array $payload): void
    {
        $url  = $object['url'];
        $type = $object['type'];

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info       = $this->repositoryHelper->getComposerInformation($repository);

        if ($info !== null) {
            return;
        }

        $context->addViolation('Url is invalid');
    }

    /**
     * @param mixed[] $object
     * @param mixed[] $payload
     */
    public function validateAddName(array $object, ExecutionContextInterface $context, array $payload): void
    {
        $url  = $object['url'];
        $type = $object['type'];

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info       = $this->repositoryHelper->getComposerInformation($repository);

        if (! isset($info['name'])) {
            $context->addViolation('composer.json does not include a valid name.');

            return;
        }

        $package = $this->registry->getRepository(Package::class)->findOneByName($info['name']);

        if ($package === null) {
            return;
        }

        $context->addViolation('Package name already exists.', [
            'name' => $package->getName(),
        ]);
    }

    /**
     * @param mixed[] $object
     * @param mixed[] $payload
     */
    public function validateEditName(array $object, ExecutionContextInterface $context, array $payload): void
    {
        $url  = $object['url'];
        $type = $object['type'];

        $package = $payload['package'];
        assert($package instanceof Package);

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info       = $this->repositoryHelper->getComposerInformation($repository);

        if (! isset($info['name'])) {
            $context->addViolation('composer.json does not include a valid name.');

            return;
        }

        if ($package->getName() !== $info['name']) {
            $context->addViolation('Package name is not accessible with this url.', [
                'name' => $package->getName(),
            ]);

            return;
        }
    }
}
