<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Package;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepositoryController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("", name="devliver_repository_")
 */
class RepositoryController extends Controller
{

    /**
     * @Route("/repo/packages.json", name="index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $json = $this->get('devliver.package_synchronization')->dumpPackagesJson($this->getUser());

        return new Response($json);
    }

    /**
     * @Route("/repo/provider/{hash}/{vendor}/{project}.json", name="provider")
     * @Route("/repo/provider", name="provider_base")
     *
     * @return Response
     */
    public function providerAction($hash, $vendor, $project)
    {
        $package = $vendor . '/' . $project;
        $packageSynchronization = $this->get('devliver.package_synchronization');

        $fileName = $packageSynchronization->getJsonMetadataPath($package);

        if (!file_exists($fileName)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        $hash2 = sha1($package . '-' . $user->getId());

        if ($hash != $hash2) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($fileName);
    }

    /**
     * @Route("/repo/dist/{vendor}/{project}/{ref}.{type}", name="dist")
     * @Route("/dist/{vendor}/{project}/{ref}.{type}", name="dist_web")
     *
     * @param Request $req
     * @param         $vendor
     * @param         $project
     * @param         $ref
     * @param         $type
     *
     * @return BinaryFileResponse|Response
     */
    public function distAction(Request $req, $vendor, $project, $ref, $type)
    {
        $packageName = $vendor . '/' . $project;
        $cacheFile = $this->get('devliver.dist_synchronization')->getDistFilename($req, $packageName, $ref, $type);

        if (empty($cacheFile)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($cacheFile, 200, [], false);
    }

    /**
     * @Route("/track-downloads", name="track_downloads")
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

        $repository = $doctrine->getRepository(Package::class);

        foreach ($postData['downloads'] as $p) {
            $name = $p['name'];

            $package = $repository->findOneBy([
                'name' => $name
            ]);

            if ($package) {
                $package->increaseDownloads();
            }

            $em->persist($package);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 201);
    }
}
