<?php

namespace Shapecode\Devliver\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Download;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DownloadController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/", name="devliver_download_")
 */
class DownloadController
{

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/track-downloads", name="track")
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function trackDownloadsAction(Request $request)
    {
        $postData = json_decode($request->getContent(), true);

        if (empty($postData['downloads']) || !is_array($postData['downloads'])) {
            throw new AccessDeniedException();
        }

        $doctrine = $this->registry;
        $em = $doctrine->getManager();

        $packageRepo = $doctrine->getRepository(Package::class);
        $versionRepo = $doctrine->getRepository(Version::class);

        foreach ($postData['downloads'] as $p) {
            $name = $p['name'];
            $versionName = $p['version'];

            $package = $packageRepo->findOneBy([
                'name' => $name,
            ]);

            if ($package) {
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

        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 201);
    }

}
