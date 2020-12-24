<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Package;
use App\Entity\Version;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use function is_array;
use function json_decode;

/**
 * @Route("/", name="devliver_download_")
 */
class DownloadController
{
    protected ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/track-downloads", name="track")
     */
    public function trackDownloadsAction(Request $request): Response
    {
        $postData = json_decode($request->getContent(), true);

        if (empty($postData['downloads']) || ! is_array($postData['downloads'])) {
            throw new AccessDeniedException();
        }

        $doctrine = $this->registry;
        $em       = $doctrine->getManager();

        $packageRepo = $doctrine->getRepository(Package::class);
        $versionRepo = $doctrine->getRepository(Version::class);

        foreach ($postData['downloads'] as $p) {
            $name        = $p['name'];
            $versionName = $p['version'];

            $package = $packageRepo->findOneBy([
                'name' => $name,
            ]);

            if (! $package) {
                continue;
            }

            $version = $versionRepo->findOneBy([
                'package' => $package->getId(),
                'name'    => $versionName,
            ]);

            $download = new Download();
            $download->setPackage($package);
            $download->setVersionName($versionName);

            if ($version) {
                $download->setVersion($version);
            }

            $em->persist($download);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 201);
    }
}
