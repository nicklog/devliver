<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Composer\ComposerManager;
use App\Entity\Download;
use App\Entity\Package;
use App\Entity\Version;
use App\Model\PackageAdapter;
use App\Repository\DownloadRepository;
use App\Service\RepositoryHelper;
use Composer\Package\CompletePackageInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function assert;

class DevliverExtension extends AbstractExtension
{
    protected ManagerRegistry $registry;

    protected UrlGeneratorInterface $router;

    protected ComposerManager $composerManager;

    protected RepositoryHelper $repositoryHelper;

    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $router,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper
    ) {
        $this->registry         = $registry;
        $this->router           = $router;
        $this->composerManager  = $composerManager;
        $this->repositoryHelper = $repositoryHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('version_downloads', [$this, 'getVersionDownloadsCounter']),
            new TwigFunction('package_downloads', [$this, 'getPackageDownloadsCounter']),
            new TwigFunction('package_download_url', [$this, 'getPackageDownloadUrl']),
            new TwigFunction('package_url', [$this, 'getPackageUrl']),
            new TwigFunction('package_adapter', [$this, 'getPackageAdapter']),
            new TwigFunction('package_readme', [$this, 'getPackageReadme'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sha1', 'sha1'),
            new TwigFilter('package_adapter', [$this, 'getPackageAdapter']),
        ];
    }

    public function getPackageDownloadsCounter(Package $package): int
    {
        $repo = $this->registry->getRepository(Download::class);
        assert($repo instanceof DownloadRepository);

        return $repo->countPackageDownloads($package);
    }

    public function getVersionDownloadsCounter(Version $version): int
    {
        $repo = $this->registry->getRepository(Download::class);
        assert($repo instanceof DownloadRepository);

        return $repo->countVersionDownloads($version);
    }

    public function getPackageAdapter(CompletePackageInterface $package): PackageAdapter
    {
        return new PackageAdapter($package);
    }

    public function getPackageUrl(string $name): string
    {
        $repo = $this->registry->getRepository(Package::class);

        $package = $repo->findOneBy([
            'name' => $name,
        ]);

        if ($package) {
            return $this->router->generate('devliver_package_view', [
                'package' => $package->getId(),
            ]);
        }

        return 'https://packagist.org/packages/' . $name;
    }

    public function getPackageDownloadUrl(CompletePackageInterface $package): string
    {
        $adapter = $this->getPackageAdapter($package);

        return $this->router->generate('devliver_repository_dist_web', [
            'vendor'  => $adapter->getVendorName(),
            'project' => $adapter->getProjectName(),
            'ref'     => $package->getSourceReference(),
            'type'    => 'zip',
        ]);
    }

    public function getPackageReadme(Package $package): ?string
    {
        $repository = $this->composerManager->createRepoByPackage(null, $package);

        return $this->repositoryHelper->getReadme($repository);
    }
}
