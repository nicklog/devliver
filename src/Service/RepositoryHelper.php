<?php

namespace Shapecode\Devliver\Service;

use Composer\Repository\RepositoryInterface;
use Composer\Repository\VcsRepository;
use Demontpx\ParsedownBundle\Parsedown;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class RepositoryHelper
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class RepositoryHelper
{

    /** @var Parsedown */
    protected $parsdown;

    /**
     * @param Parsedown $parsdown
     */
    public function __construct(Parsedown $parsdown)
    {
        $this->parsdown = $parsdown;
    }

    /**
     * @param RepositoryInterface $repository
     *
     * @return null|string
     */
    public function getReadme(RepositoryInterface $repository)
    {
        if (!($repository instanceof VcsRepository)) {
            return null;
        }

        $driver = $repository->getDriver();
        $composerInfo = $driver->getComposerInformation($driver->getRootIdentifier());

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
}
