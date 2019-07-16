<?php

namespace Shapecode\Devliver\Controller;

use Shapecode\Devliver\Form\Type\Forms\SetupType;
use Shapecode\Devliver\Model\Setup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SetupController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 *
 * @Route("/setup", name="devliver_setup_")
 */
class SetupController extends AbstractController
{

    /**
     * @Route("/", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $setup = Setup::create();

        $form = $this->createForm(SetupType::class, $setup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            dd($data);
        }

        return $this->render('setup/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
