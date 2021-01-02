<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Form\Type\Forms\ClientType;
use App\Repository\ClientRepository;
use App\Service\FlashBagHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use function assert;

/**
 * @Route("/clients", name="app_client_")
 * @IsGranted("ROLE_ADMIN")
 */
final class ClientController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private ClientRepository $clientRepository;

    private FlashBagHelper $flashBagHelper;

    private PaginatorInterface $paginator;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository,
        FlashBagHelper $flashBagHelper,
        PaginatorInterface $paginator,
        UserPasswordEncoderInterface $userPasswordEncoder,
    ) {
        $this->entityManager       = $entityManager;
        $this->clientRepository    = $clientRepository;
        $this->flashBagHelper      = $flashBagHelper;
        $this->paginator           = $paginator;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @Route("", name="index")
     */
    public function index(Request $request): Response
    {
        $qb = $this->clientRepository->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);

        $sort      = $request->query->get('sort', 'p.id');
        $direction = $request->query->get('direction', 'asc');

        $qb->orderBy($sort, $direction);

        $pagination = $this->paginator->paginate($qb, $page);

        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request): Response
    {
        $form = $this->createForm(ClientType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            assert($client instanceof Client);

            $client->setPassword(
                $this->userPasswordEncoder->encodePassword($client, $client->getToken())
            );

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $this->flashBagHelper->success('Client created');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{client}/edit", name="edit")
     */
    public function edit(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setPassword(
                $this->userPasswordEncoder->encodePassword($client, $client->getToken())
            );

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $this->flashBagHelper->success('Client updated');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{client}/remove", name="remove")
     */
    public function remove(Request $request, Client $client): Response
    {
        $this->entityManager->remove($client);
        $this->entityManager->flush();

        $this->flashBagHelper->success('Client removed');

        return $this->redirectToRoute('app_client_index');
    }

    /**
     * @Route("/{client}/enable", name="enable")
     */
    public function enable(Request $request, Client $client): Response
    {
        $client->setEnable(true);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->flashBagHelper->success('Client enabled');

        return $this->redirectToRoute('app_client_index');
    }

    /**
     * @Route("/{client}/disable", name="disable")
     */
    public function disable(Request $request, Client $client): Response
    {
        $client->setEnable(false);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->flashBagHelper->success('Client disabled');

        return $this->redirectToRoute('app_client_index');
    }

    /**
     * @Route("/{client}/reset-token", name="reset_token")
     */
    public function resetToken(Request $request, Client $client): Response
    {
        $client
            ->setToken(Uuid::uuid4()->toString())
            ->setPassword(
                $this->userPasswordEncoder->encodePassword($client, $client->getToken())
            );

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->flashBagHelper->success('Client token reseted');

        return $this->redirectToRoute('app_client_index');
    }
}
