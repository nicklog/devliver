<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Download;
use App\Entity\Package;
use App\Entity\Version;
use App\Model\PackageAdapter;
use Composer\Package\CompletePackageInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gravatar\Gravatar;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class DevliverExtension extends AbstractExtension
{
    private ManagerRegistry $registry;

    private UrlGeneratorInterface $router;

    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $router,
    ) {
        $this->registry = $registry;
        $this->router   = $router;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('version_downloads', [$this, 'getVersionDownloadsCounter']),
            new TwigFunction('package_downloads', [$this, 'getPackageDownloadsCounter']),
            new TwigFunction('package_download_url', [$this, 'getPackageDownloadUrl']),
            new TwigFunction('package_url', [$this, 'getPackageUrl']),
            new TwigFunction('package_adapter', [$this, 'getPackageAdapter']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sha1', 'sha1'),
            new TwigFilter('package_adapter', [$this, 'getPackageAdapter']),
            new TwigFilter('gravatar', [$this, 'getGravatarUrl']),
        ];
    }

    public function getPackageDownloadsCounter(Package $package): int
    {
        $repo = $this->registry->getRepository(Download::class);

        return $repo->countPackageDownloads($package);
    }

    public function getVersionDownloadsCounter(Version $version): int
    {
        $repo = $this->registry->getRepository(Download::class);

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

        if ($package !== null) {
            return $this->router->generate('app_package_view', [
                'package' => $package->getId(),
            ]);
        }

        return 'https://packagist.org/packages/' . $name;
    }

    public function getPackageDownloadUrl(CompletePackageInterface $package): string
    {
        $adapter = $this->getPackageAdapter($package);

        return $this->router->generate('app_repository_dist_web', [
            'vendor'  => $adapter->getVendorName(),
            'project' => $adapter->getProjectName(),
            'ref'     => $package->getSourceReference(),
            'type'    => 'zip',
        ]);
    }

    public function getGravatarUrl(string $email, int $size): string
    {
        $gravatar = new Gravatar([], true);

        return $gravatar->avatar($email, ['s' => $size]);
    }
}
