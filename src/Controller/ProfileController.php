<?php

namespace Shapecode\Devliver\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Form\Type\Forms\UserProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class ProfileController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/profile", name="devliver_profile_")
 */
class ProfileController extends AbstractController
{

    /** @var ManagerRegistry */
    protected $registry;

    /** @var Security */
    protected $security;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param ManagerRegistry      $registry
     * @param Security             $security
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ManagerRegistry $registry, Security $security, FormFactoryInterface $formFactory)
    {
        $this->registry = $registry;
        $this->security = $security;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("", name="index")
     *
     * @return Response
     */
    public function profileAction(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/edit", name="edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request): Response
    {
        $user = $this->security->getUser();

        $form = $this->formFactory->create(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute($request->get('_route'));
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }
}
