<?php

declare(strict_types=1);

namespace App\Service;

use Composer\Repository\RepositoryInterface;
use Composer\Repository\VcsRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

use function htmlspecialchars;
use function str_replace;

class RepositoryHelper
{
    protected UrlGeneratorInterface $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function getReadme(RepositoryInterface $repository): ?string
    {
        if (! ($repository instanceof VcsRepository)) {
            return null;
        }

        try {
            $driver = $repository->getDriver();
        } catch (Throwable $e) {
            return null;
        }

        $composerInfo = $this->getComposerInformation($repository);

        $readme = $composerInfo['readme'] ?? 'README.md';

        $file = new File($readme, false);
        $ext  = $file->getExtension();

        $source = null;

        try {
            switch ($ext) {
                case 'md':
                    $source = $driver->getFileContent($readme, $driver->getRootIdentifier());
                    break;
                default:
                    $source = $driver->getFileContent($readme, $driver->getRootIdentifier());
                    if (! empty($source)) {
                        $source = '<pre>' . htmlspecialchars($source) . '</pre>';
                    }

                    break;
            }
        } catch (Throwable $e) {
        }

        return $source;
    }

    /**
     * @return mixed[]|null
     */
    public function getComposerInformation(RepositoryInterface $repository): ?array
    {
        if (! ($repository instanceof VcsRepository)) {
            return null;
        }

        try {
            $driver = $repository->getDriver();
        } catch (Throwable $e) {
            return null;
        }

        if ($driver === null) {
            return null;
        }

        return $driver->getComposerInformation($driver->getRootIdentifier());
    }

    public function getComposerDistUrl(
        string $package,
        string $ref,
        string $type = 'zip'
    ): string {
        $distUrl = $this->router->generate('devliver_repository_dist', [
            'vendor'  => 'PACK',
            'project' => 'AGE',
            'ref'     => 'REF',
            'type'    => 'TYPE',
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $distUrl = str_replace(
            ['PACK/AGE', 'REF', 'TYPE'],
            [$package, $ref, $type],
            $distUrl
        );

        return $distUrl;
    }
}
