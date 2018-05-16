<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Package;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $doctrine = $this->getDoctrine();
        $user = $this->getUser();

        $name = $vendor . '/' . $project;
        $hash2 = sha1($name . '-' . $user->getId());

        if ($hash != $hash2) {
            throw $this->createAccessDeniedException();
        }

        $packageSynchronization = $this->get('devliver.package_synchronization');

        $repository = $doctrine->getRepository(Package::class);
        $package = $repository->findOneByName($name);

        if ($package === null) {
            throw $this->createNotFoundException();
        }

        $json = $packageSynchronization->dumpPackageJson($user, $package);

        return new Response($json);
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
    public function distAction($vendor, $project, $ref, $type)
    {
        $doctrine = $this->getDoctrine();

        $name = $vendor . '/' . $project;

        $repository = $doctrine->getRepository(Package::class);
        $package = $repository->findOneByName($name);

        if ($package === null) {
            throw $this->createNotFoundException();
        }

        $cacheFile = $this->get('devliver.dist_synchronization')->getDistFilename($package, $ref);

        if (empty($cacheFile)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($cacheFile, 200, [], false);
    }

}
