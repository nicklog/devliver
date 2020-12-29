<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Service\DistSynchronization;
use App\Service\PackagesDumper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="app_repository_")
 */
final class RepositoryController extends AbstractController
{
    private ManagerRegistry $registry;

    private PackagesDumper $packagesDumper;

    private DistSynchronization $distSync;

    public function __construct(
        ManagerRegistry $registry,
        PackagesDumper $packagesDumper,
        DistSynchronization $distSync,
    ) {
        $this->registry       = $registry;
        $this->packagesDumper = $packagesDumper;
        $this->distSync       = $distSync;
    }

    /**
     * @Route("/repo/packages.json", name="index")
     */
    public function index(): Response
    {
        $json = $this->packagesDumper->dumpPackagesJson();

        return new Response($json);
    }

    /**
     * @Route("/repo/provider/{vendor}/{project}.json", name="provider")
     * @Route("/repo/provider", name="provider_base")
     */
    public function provider(string $vendor, string $project): Response
    {
        $name = $vendor . '/' . $project;

        $repository = $this->registry->getRepository(Package::class);

        $package = $repository->findOneByName($name);

        if ($package === null) {
            throw new NotFoundHttpException();
        }

        $json = $this->packagesDumper->dumpPackageJson($package);

        return new Response($json);
    }

    /**
     * @Route("/repo/dist/{vendor}/{project}/{ref}.{type}", name="dist")
     * @Route("/dist/{vendor}/{project}/{ref}.{type}", name="dist_web")
     */
    public function dist(
        string $vendor,
        string $project,
        string $ref,
        string $type
    ): BinaryFileResponse {
        $name = $vendor . '/' . $project;

        $repository = $this->registry->getRepository(Package::class);
        $package    = $repository->findOneByName($name);

        if ($package === null) {
            throw $this->createNotFoundException();
        }

        $cacheFile = $this->distSync->getDistFilename($package, $ref);

        if ($cacheFile === null) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($cacheFile, 200, [], false);
    }
}
