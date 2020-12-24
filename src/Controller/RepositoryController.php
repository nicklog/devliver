<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Service\DistSynchronization;
use App\Service\PackagesDumper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

use function assert;

/**
 * @Route("", name="devliver_repository_")
 */
class RepositoryController
{
    protected ManagerRegistry $registry;

    protected PackagesDumper $packagesDumper;

    protected DistSynchronization $distSync;

    protected Security $security;

    public function __construct(
        ManagerRegistry $registry,
        PackagesDumper $packagesDumper,
        DistSynchronization $distSync,
        Security $security
    ) {
        $this->registry       = $registry;
        $this->packagesDumper = $packagesDumper;
        $this->distSync       = $distSync;
        $this->security       = $security;
    }

    /**
     * @Route("/repo/packages.json", name="index")
     */
    public function indexAction(): Response
    {
        $json = $this->packagesDumper->dumpPackagesJson($this->security->getUser());

        return new Response($json);
    }

    /**
     * @Route("/repo/provider/{vendor}/{project}.json", name="provider")
     * @Route("/repo/provider", name="provider_base")
     */
    public function providerAction(string $vendor, string $project): Response
    {
        $user = $this->security->getUser();

        $name = $vendor . '/' . $project;

        $repository = $this->registry->getRepository(Package::class);

        $package = $repository->findOneByName($name);
        assert($package instanceof Package || $package === null);

        if ($package === null) {
            throw new NotFoundHttpException();
        }

        $json = $this->packagesDumper->dumpPackageJson($user, $package);

        return new Response($json);
    }

    /**
     * @Route("/repo/dist/{vendor}/{project}/{ref}.{type}", name="dist")
     * @Route("/dist/{vendor}/{project}/{ref}.{type}", name="dist_web")
     */
    public function distAction(
        string $vendor,
        string $project,
        string $ref,
        string $type
    ): BinaryFileResponse {
        $name = $vendor . '/' . $project;

        $repository = $this->registry->getRepository(Package::class);
        $package    = $repository->findOneByName($name);

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
