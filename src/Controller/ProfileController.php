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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/profile", name="app_profile_")
 */
final class ProfileController extends AbstractController
{
    private ManagerRegistry $registry;

    private Security $security;

    private FormFactoryInterface $formFactory;

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
    public function profile(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/edit", name="edit")
     */
    public function edit(
        UserInterface $user,
        Request $request
    ): Response {
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
