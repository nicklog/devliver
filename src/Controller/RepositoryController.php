<?php

namespace Shapecode\Devliver\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Service\DistSynchronization;
use Shapecode\Devliver\Service\PackagesDumper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class RepositoryController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("", name="devliver_repository_")
 */
class RepositoryController
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var PackagesDumper */
    protected $packagesDumper;

    /** @var DistSynchronization */
    protected $distSync;

    /** @var Security */
    protected $security;

    /**
     * @param ManagerRegistry     $registry
     * @param PackagesDumper      $packagesDumper
     * @param DistSynchronization $distSync
     * @param Security            $security
     */
    public function __construct(ManagerRegistry $registry, PackagesDumper $packagesDumper, DistSynchronization $distSync, Security $security)
    {
        $this->registry = $registry;
        $this->packagesDumper = $packagesDumper;
        $this->distSync = $distSync;
        $this->security = $security;
    }

    /**
     * @Route("/repo/packages.json", name="index")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $json = $this->packagesDumper->dumpPackagesJson($this->security->getUser());

        return new Response($json);
    }

    /**
     * @Route("/repo/provider/{vendor}/{project}.json", name="provider")
     * @Route("/repo/provider", name="provider_base")
     *
     * @param $vendor
     * @param $project
     *
     * @return Response
     */
    public function providerAction($vendor, $project): Response
    {
        $user = $this->security->getUser();

        $name = $vendor.'/'.$project;

        $repository = $this->registry->getRepository(Package::class);

        /** @var Package|null $package */
        $package = $repository->findOneByName($name);

        if ($package === null) {
            throw new NotFoundHttpException();
        }

        $json = $this->packagesDumper->dumpPackageJson($user, $package);

        return new Response($json);
    }

    /**
     * @Route("/repo/dist/{vendor}/{project}/{ref}.{type}", name="dist")
     * @Route("/dist/{vendor}/{project}/{ref}.{type}", name="dist_web")
     *
     * @param         $vendor
     * @param         $project
     * @param         $ref
     * @param         $type
     *
     * @return BinaryFileResponse
     */
    public function distAction($vendor, $project, $ref, $type): BinaryFileResponse
    {
        $name = $vendor.'/'.$project;

        $repository = $this->registry->getRepository(Package::class);
        $package = $repository->findOneByName($name);

        if ($package === null) {
            throw new NotFoundHttpException();
        }

        $cacheFile = $this->distSync->getDistFilename($package, $ref);

        if (empty($cacheFile)) {
            throw new NotFoundHttpException();
        }

        return new BinaryFileResponse($cacheFile, 200, [], false);
    }

}
