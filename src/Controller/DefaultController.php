<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="devliver_")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function indexAction(): Response
    {
        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/packages.json", name="packages")
     * @Route("/repo/private/packages.json", name="toran_fallback")
     * @Route("/repo/private", name="toran_fallback_2")
     */
    public function packagesAction(Request $request): Response
    {
        return $this->redirectToRoute('devliver_repository_index', $request->query->all());
    }

    /**
     * @Route("/how-to", name="howto")
     */
    public function howtoAction(): Response
    {
        return $this->render('default/howto.html.twig');
    }
}
