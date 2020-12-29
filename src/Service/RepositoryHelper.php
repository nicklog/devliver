<?php

declare(strict_types=1);

namespace App\Service;

use Composer\Repository\RepositoryInterface;
use Composer\Repository\Vcs\VcsDriverInterface;
use Composer\Repository\VcsRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use function htmlspecialchars;
use function str_replace;

final class RepositoryHelper
{
    private UrlGeneratorInterface $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function getReadme(RepositoryInterface $repository): ?string
    {
        if (!($repository instanceof VcsRepository)) {
            return null;
        }

        try {
            $driver = $repository->getDriver();
        } catch (Throwable $e) {
            return null;
        }

        $composerInfo = $this->getComposerInformation($repository);

        $readmes = [
            'README.md',
            'README',
            'docs/README.md',
            'docs/README',
        ];

        if (isset($composerInfo['readme'])) {
            array_unshift($readmes, $composerInfo['readme']);
        }

        foreach ($readmes as $readme) {
            try {
                $source = $this->getReadmeSource($driver, $readme);
                
                if($source !== null) {
                    return $source;
                }
            } catch (Throwable $e) {
            }
        }

        return null;
    }

    private function getReadmeSource(VcsDriverInterface $driver, $path): ?string
    {
        $file = new File($path, false);
        $ext = $file->getExtension();

        if ($ext === 'md') {
            $source = $driver->getFileContent($path, $driver->getRootIdentifier());
        } else {
            $source = $driver->getFileContent($path, $driver->getRootIdentifier());
            if ($source !== null) {
                $source = '<pre>' . htmlspecialchars($source) . '</pre>';
            }
        }

        return $source;
    }

    /**
     * @return mixed[]|null
     */
    public function getComposerInformation(RepositoryInterface $repository): ?array
    {
        if (!($repository instanceof VcsRepository)) {
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
        $distUrl = $this->router->generate('app_repository_dist', [
            'vendor' => 'PACK',
            'project' => 'AGE',
            'ref' => 'REF',
            'type' => 'TYPE',
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $distUrl = str_replace(
            [
                'PACK/AGE',
                'REF',
                'TYPE',
            ],
            [
                $package,
                $ref,
                $type,
            ],
            $distUrl
        );

        return $distUrl;
    }
}
