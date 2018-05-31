<?php

namespace Shapecode\Devliver\Service;

use Composer\Repository\RepositoryInterface;
use Composer\Repository\VcsRepository;
use Demontpx\ParsedownBundle\Parsedown;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class RepositoryHelper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 */
class RepositoryHelper
{

    /** @var Parsedown */
    protected $parsdown;

    /** @var UrlGeneratorInterface */
    protected $router;

    /**
     * @param Parsedown             $parsdown
     * @param UrlGeneratorInterface $router
     */
    public function __construct(Parsedown $parsdown, UrlGeneratorInterface $router)
    {
        $this->parsdown = $parsdown;
        $this->router = $router;
    }

    /**
     * @param RepositoryInterface $repository
     *
     * @return null|string
     */
    public function getReadme(RepositoryInterface $repository): ?string
    {
        if (!($repository instanceof VcsRepository)) {
            return null;
        }

        try {
            $driver = $repository->getDriver();
        } catch (\Exception $e) {
            return null;
        }

        $composerInfo = $this->getComposerInformation($repository);

        $readme = $composerInfo['readme'] ?? 'README.md';

        $file = new File($readme, false);
        $ext = $file->getExtension();

        $source = null;

        try {
            switch ($ext) {
                case 'md':
                    $source = $driver->getFileContent($readme, $driver->getRootIdentifier());
                    $source = $this->parsdown->parse($source);
                    break;
                default:
                    $source = $driver->getFileContent($readme, $driver->getRootIdentifier());
                    if (!empty($source)) {
                        $source = '<pre>' . htmlspecialchars($source) . '</pre>';
                    }
                    break;
            }
        } catch (\Exception $e) {
        }

        return $source;
    }

    /**
     * @param RepositoryInterface $repository
     *
     * @return array|null
     */
    public function getComposerInformation(RepositoryInterface $repository): ?array
    {
        if (!($repository instanceof VcsRepository)) {
            return null;
        }

        try {
            $driver = $repository->getDriver();
        } catch (\Exception $e) {
            return null;
        }

        if ($driver === null) {
            return null;
        }

        return $driver->getComposerInformation($driver->getRootIdentifier());
    }

    /**
     * @param                       $package
     * @param                       $ref
     * @param                       $type
     *
     * @return string
     */
    public function getComposerDistUrl($package, $ref, $type = 'zip'): string
    {
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
