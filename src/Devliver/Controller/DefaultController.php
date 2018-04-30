<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        return $this->redirectToRoute('devliver_repo_index');
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
     * @Route("/usage", name="usage")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function usageAction()
    {
        $packagesUrl = $this->generateUrl('devliver_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $content = file_get_contents('https://raw.githubusercontent.com/shapecode/devliver/master/README.md');

        $content = str_replace('https://yourdomain.url', $packagesUrl, $content);

        return $this->render('@Devliver/Home/usage.html.twig', [
            'page'  => 'home',
            'usage' => $content
        ]);
    }
}