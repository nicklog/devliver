<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("", name="devliver_")
 */
class DefaultController extends Controller
{

    /**
     * @Route("", name="index")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/packages.json", name="packages")
     * @Route("/repo/private/packages.json", name="toran_fallback")
     * @Route("/repo/private", name="toran_fallback_2")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function packagesAction()
    {
        return $this->redirectToRoute('devliver_repository_index');
    }

    /**
     * @Route("/how-to", name="howto")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function howtoAction()
    {
        return [];
    }
}