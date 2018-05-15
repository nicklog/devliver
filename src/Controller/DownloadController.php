<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Download;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DownloadController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @Route("", name="devliver_download_")
 */
class DownloadController extends Controller
{

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
            throw $this->createAccessDeniedException();
        }

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        $packageRepo = $doctrine->getRepository(Package::class);
        $versionRepo = $doctrine->getRepository(Version::class);

        foreach ($postData['downloads'] as $p) {
            $name = $p['name'];
            $versionName = $p['version'];

            $package = $packageRepo->findOneBy([
                'name' => $name
            ]);

            $version = $versionRepo->findOneBy([
                'package' => $package->getId(),
                'name'    => $versionName,
            ]);

            if ($package) {
                $download = new Download();
                $download->setPackage($package);

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
