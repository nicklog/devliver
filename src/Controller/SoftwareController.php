<?php

namespace Shapecode\Devliver\Controller;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Loader\ArrayLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
class SoftwareController
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
     * @Template()
     *
     * @return RedirectResponse|Response|array
     */
    public function indexAction()
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

        return [
            'packages' => $packages,
        ];
    }
}
