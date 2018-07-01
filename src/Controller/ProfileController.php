<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Shapecode\Devliver\Form\Type\Forms\UserProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfileController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/profile", name="devliver_profile_")
 */
class ProfileController extends Controller
{

    /**
     * @Route("", name="index")
     * @Template()
     *
     * @return array
     */
    public function profileAction()
    {
        return [
            'user' => $this->getUser()
        ];
    }

    /**
     * @Route("/edit", name="edit")
     * @Template()
     *
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute($request->get('_route'));
        }

        return [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ];
    }
}
