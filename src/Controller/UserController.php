<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\Forms\UserType;
use App\Repository\UserRepository;
use App\Service\FlashBagHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use function assert;

/**
 * @Route("/users", name="app_user_")
 * @IsGranted("ROLE_ADMIN")
 */
final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private UserRepository $userRepository;

    private FlashBagHelper $flashBagHelper;

    private PaginatorInterface $paginator;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        FlashBagHelper $flashBagHelper,
        PaginatorInterface $paginator,
        UserPasswordEncoderInterface $userPasswordEncoder,
    ) {
        $this->entityManager       = $entityManager;
        $this->userRepository      = $userRepository;
        $this->flashBagHelper      = $flashBagHelper;
        $this->paginator           = $paginator;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        $qb = $this->userRepository->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);

        $sort      = $request->query->get('sort', 'p.id');
        $direction = $request->query->get('direction', 'asc');

        $qb->orderBy($sort, $direction);

        $pagination = $this->paginator->paginate($qb, $page);

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            assert($user instanceof User);

            $password = $form->get('password')->getData();
            if ($password !== null && $password !== '') {
                $user->setPassword($this->userPasswordEncoder->encodePassword($user, $password));
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->flashBagHelper->success('User created');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{user}/edit", name="edit")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if ($password !== null && $password !== '') {
                $user->setPassword($this->userPasswordEncoder->encodePassword($user, $password));
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->flashBagHelper->success('User updated');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{user}/remove", name="remove")
     */
    public function remove(Request $request, User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->flashBagHelper->success('User removed');

        return $this->redirectToRoute('app_user_index');
    }

    /**
     * @Route("/{user}/enable", name="enable")
     */
    public function enable(Request $request, User $user): Response
    {
        $user->setEnable(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->flashBagHelper->success('User enabled');

        return $this->redirectToRoute('app_user_index');
    }

    /**
     * @Route("/{user}/disable", name="disable")
     */
    public function disable(Request $request, User $user): Response
    {
        $user->setEnable(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->flashBagHelper->success('User enabled');

        return $this->redirectToRoute('app_user_index');
    }
}
