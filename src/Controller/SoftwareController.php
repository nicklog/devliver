<?php

declare(strict_types=1);

namespace App\Controller;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Loader\ArrayLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function file_get_contents;
use function json_decode;

/**
 * @Route("/software", name="devliver_software_")
 */
class SoftwareController extends AbstractController
{
    protected string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction(): Response
    {
        $lock = $this->projectDir . '/composer.lock';

        $json = file_get_contents($lock);
        $data = json_decode($json, true);

        /** @var CompletePackageInterface[] $packages */
        $packages = [];

        $loader = new ArrayLoader();

        foreach ($data['packages'] as $p) {
            $package = $loader->load($p);

            $packages[] = $package;
        }

        return $this->render('software/index.html.twig', [
            'packages' => $packages,
        ]);
    }
}
