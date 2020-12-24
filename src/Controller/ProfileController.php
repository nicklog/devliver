<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\Forms\UserProfileType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/profile", name="devliver_profile_")
 */
class ProfileController extends AbstractController
{
    protected ManagerRegistry $registry;

    protected Security $security;

    protected FormFactoryInterface $formFactory;

    public function __construct(
        ManagerRegistry $registry,
        Security $security,
        FormFactoryInterface $formFactory
    ) {
        $this->registry    = $registry;
        $this->security    = $security;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("", name="index")
     */
    public function profileAction(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/edit", name="edit")
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
