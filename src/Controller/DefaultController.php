<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function file_get_contents;

/**
 * @Route("", name="app_")
 */
final class DefaultController extends AbstractController
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @Route("", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/packages.json", name="packages")
     * @Route("/repo/private/packages.json", name="toran_fallback")
     * @Route("/repo/private", name="toran_fallback_2")
     */
    public function packages(Request $request): Response
    {
        return $this->redirectToRoute('app_repository_index', $request->query->all());
    }

    /**
     * @Route("/how-to", name="howto")
     */
    public function howto(): Response
    {
        $readme = file_get_contents($this->projectDir . '/docs/README.md');

        return $this->render('default/howto.html.twig', [
            'readme' => $readme,
        ]);
    }
}
