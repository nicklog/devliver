<?php

namespace Shapecode\Devliver\Controller;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Loader\ArrayLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SoftwareController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/software", name="devliver_software_")
 */
class SoftwareController extends AbstractController
{

    /** @var string */
    protected $projectDir;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $lock = $this->projectDir.'/composer.lock';

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
