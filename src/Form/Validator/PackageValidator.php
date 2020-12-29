<?php

declare(strict_types=1);

namespace App\Form\Validator;

use App\Composer\ComposerManager;
use App\Entity\Package;
use App\Service\RepositoryHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PackageValidator
{
    private ComposerManager $composerManager;

    private RepositoryHelper $repositoryHelper;

    private ManagerRegistry $registry;

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
     * @param mixed[]|null $payload
     */
    public function validateRepository(?Package $object, ExecutionContextInterface $context, ?array $payload): void
    {
        if ($object === null) {
            return;
        }

        $url  = $object->getUrl();
        $type = $object->getType();

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info       = $this->repositoryHelper->getComposerInformation($repository);

        if ($info !== null) {
            return;
        }

        $context->addViolation('Url is invalid');
    }

    /**
     * @param mixed[]|null $payload
     */
    public function validateAddName(?Package $object, ExecutionContextInterface $context, ?array $payload): void
    {
        if ($object === null) {
            return;
        }

        $url  = $object->getUrl();
        $type = $object->getType();

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

    public function validateEditName(Package $object, ExecutionContextInterface $context, Package $payload): void
    {
        $url  = $object->getUrl();
        $type = $object->getType();

        $repository = $this->composerManager->createRepositoryByUrl($url, $type);
        $info       = $this->repositoryHelper->getComposerInformation($repository);

        if (! isset($info['name'])) {
            $context->addViolation('composer.json does not include a valid name.');

            return;
        }

        if ($payload->getName() !== $info['name']) {
            $context->addViolation('Package name is not accessible with this url.', [
                'name' => $payload->getName(),
            ]);

            return;
        }
    }
}
