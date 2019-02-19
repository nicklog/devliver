<?php

namespace Shapecode\Devliver\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Shapecode\Devliver\Form\Type\Forms\UserProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @Template()
     *
     * @return array
     */
    public function profileAction()
    {
        return [
            'user' => $this->security->getUser()
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
        $user = $this->security->getUser();

        $form = $this->formFactory->create(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

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
