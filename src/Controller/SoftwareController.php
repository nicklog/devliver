<?php

namespace Shapecode\Devliver\Controller;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Loader\ArrayLoader;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class SoftwareController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/software", name="devliver_software_")
 */
class SoftwareController extends Controller
{

    /**
     * @Route("/", name="index")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $kernel = $this->get('kernel');
        $projectDir = $kernel->getProjectDir();
        $lock = $projectDir . '/composer.lock';

        $json = file_get_contents($lock);
        $data = json_decode($json, true);

        /** @var CompletePackageInterface[] $packages */
        $packages = [];

        $loader = new ArrayLoader();

        foreach ($data['packages'] as $p) {
            $package = $loader->load($p);

            $packages[] = $package;
        }

        return $this->render('@Devliver/Software/index.html.twig', [
            'packages' => $packages
        ]);
    }
}
