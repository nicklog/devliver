<?php

namespace Shapecode\Devliver\Twig\Extension;

use Composer\Package\CompletePackageInterface;
use Github\Client as GithubClient;
use Shapecode\Devliver\Model\PackageAdapter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class DevliverExtension
 *
 * @package Shapecode\Devliver\Twig\Extension
 * @author  Nikita Loges
 */
class DevliverExtension extends AbstractExtension implements GlobalsInterface
{

    /** @var string */
    protected $currentVersion;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var GithubClient */
    protected $github;

    /**
     * @param UrlGeneratorInterface $router
     * @param                       $currentVersion
     * @param GithubClient          $github
     */
    public function __construct(UrlGeneratorInterface $router, $currentVersion, GithubClient $github)
    {
        $this->router = $router;
        $this->currentVersion = $currentVersion;
        $this->github = $github;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('package_download_url', [$this, 'getPackageDownloadUrl']),
            new TwigFunction('package_adapter', [$this, 'getPackageAdapter'])
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sha1', 'sha1'),
            new TwigFilter('package_adapter', [$this, 'getPackageAdapter'])
        ];
    }

    /**
     * @param CompletePackageInterface $package
     *
     * @return PackageAdapter
     */
    public function getPackageAdapter(CompletePackageInterface $package)
    {
        return new PackageAdapter($package);
    }

    /**
     * @param CompletePackageInterface $package
     *
     * @return string
     */
    public function getPackageDownloadUrl(CompletePackageInterface $package)
    {
        $adapter = $this->getPackageAdapter($package);

        return $this->router->generate('devliver_repository_dist_web', [
            'vendor'  => $adapter->getVendorName(),
            'project' => $adapter->getProjectName(),
            'ref'     => $package->getSourceReference(),
            'type'    => 'zip',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        $client = $this->github;

        $latest = $client->api('repo')->releases()->all('shapecode', 'devliver')[0];

        return [
            'current_release' => $this->currentVersion,
            'latest_release'  => $latest,
        ];
    }

}
