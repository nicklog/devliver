<?php

declare(strict_types=1);

namespace App\Controller;

use App\Composer\ComposerManager;
use App\Entity\Package;
use App\Form\Type\Forms\PackageAbandonType;
use App\Form\Type\Forms\PackageType;
use App\Form\Validator\PackageValidator;
use App\Service\PackageSynchronization;
use App\Service\RepositoryHelper;
use Cocur\Slugify\Slugify;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * @Route("/package", name="devliver_package_")
 */
class PackageController extends AbstractController
{
    protected PackageValidator $packageValidator;

    protected ManagerRegistry $registry;

    protected PaginatorInterface $paginator;

    protected FormFactoryInterface $formFactory;

    protected FlashBagInterface $flashBag;

    protected ComposerManager $composerManager;

    protected RepositoryHelper $repositoryHelper;

    protected PackageSynchronization $packageSynchronization;

    protected Slugify $slugify;

    public function __construct(
        PackageValidator $packageValidator,
        ManagerRegistry $registry,
        PaginatorInterface $paginator,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper,
        Slugify $slugify,
        PackageSynchronization $packageSynchronization
    ) {
        $this->packageValidator       = $packageValidator;
        $this->registry               = $registry;
        $this->paginator              = $paginator;
        $this->formFactory            = $formFactory;
        $this->flashBag               = $flashBag;
        $this->composerManager        = $composerManager;
        $this->repositoryHelper       = $repositoryHelper;
        $this->slugify                = $slugify;
        $this->packageSynchronization = $packageSynchronization;
    }

    /**
     * @Route("s", name="index")
     */
    public function listAction(Request $request): Response
    {
        $repository = $this->registry->getRepository(Package::class);
        $qb         = $repository->createQueryBuilder('p');

        $page  = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $sort      = $request->query->get('sort', 'p.name');
        $direction = $request->query->get('direction', 'asc');
        $filter    = $request->query->get('filter');

        $qb->orderBy($sort, $direction);

        if (! empty($filter)) {
            $qb->orWhere($qb->expr()->like('p.name', ':filter'));
            $qb->orWhere($qb->expr()->like('p.readme', ':filter'));
            $qb->orWhere($qb->expr()->like('p.url', ':filter'));
            $qb->orWhere($qb->expr()->like('p.replacementPackage', ':filter'));
            $qb->setParameter('filter', '%' . $filter . '%');
        }

        $pagination = $this->paginator->paginate($qb, $page, $limit);

        return $this->render('package/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{package}/abandon", name="abandon")
     */
    public function abandonAction(Request $request, Package $package): Response
    {
        $form = $this->formFactory->create(PackageAbandonType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

            $package->setAbandoned(true);
            $em->persist($package);
            $em->flush();

            $this->flashBag->add('success', 'Package abandoned');

            return $this->redirectToRoute('devliver_package_index');
        }

        return $this->render('package/abandon.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{package}/unabandon", name="unabandon")
     */
    public function unabandonAction(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setAbandoned(false);
        $package->setReplacementPackage(null);

        $em->persist($package);
        $em->flush();

        $this->flashBag->add('success', 'Package unabandoned');

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}/enable", name="enable")
     */
    public function enableAction(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setEnable(true);

        $em->persist($package);
        $em->flush();

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}/disable", name="disable")
     */
    public function disableAction(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setEnable(false);

        $em->persist($package);
        $em->flush();

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/add", name="add")
     */
    public function addAction(Request $request): Response
    {
        $form = $this->formFactory->create(PackageType::class, null, [
            'constraints' => [
                new Callback([$this->packageValidator, 'validateRepository']),
                new Callback([$this->packageValidator, 'validateAddName']),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em   = $this->registry->getManager();
            $data = $form->getData();

            $url  = $data['url'];
            $type = $data['type'];

            $repository = $this->composerManager->createRepositoryByUrl($url, $type);
            $info       = $this->repositoryHelper->getComposerInformation($repository);

            $package = new Package();
            $package->setUrl($url);
            $package->setType($type);
            $package->setName($info['name']);

            $em->persist($package);
            $em->flush();

            $this->packageSynchronization->sync($package);

            $this->flashBag->add('success', 'Package added');

            return $this->redirectToRoute('devliver_package_index');
        }

        return $this->render('package/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{package}/edit", name="edit")
     */
    public function editAction(Request $request, Package $package): Response
    {
        $form = $this->createForm(PackageType::class, $package->getConfig(), [
            'constraints' => [
                new Callback([$this->packageValidator, 'validateRepository']),
                new Callback([
                    'callback' => [$this->packageValidator, 'validateEditName'],
                    'payload'  => ['package' => $package],
                ]),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em   = $this->registry->getManager();
            $data = $form->getData();

            $url  = $data['url'];
            $type = $data['type'];

            if ($url !== $package->getUrl() || $type !== $package->getType()) {
                $package->setUrl($url);
                $package->setType($type);
                $package->setAutoUpdate(false);

                $em->persist($package);
                $em->flush();

                $this->packageSynchronization->sync($package);
            }

            $this->flashBag->add('success', 'Package updated');

            return $this->redirectToRoute('devliver_package_edit', [
                'package' => $package->getId(),
            ]);
        }

        return $this->render('package/edit.html.twig', [
            'form'    => $form->createView(),
            'package' => $package,
        ]);
    }

    /**
     * @Route("/{package}/delete", name="delete")
     */
    public function deleteAction(Package $package): Response
    {
        $em = $this->registry->getManager();

        $em->remove($package);
        $em->flush();

        $this->flashBag->add('success', 'Package removed');

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}/update", name="update")
     */
    public function updateAction(Request $request, Package $package): Response
    {
        $this->packageSynchronization->sync($package);

        $this->flashBag->add('success', 'Package updated');

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_view', [
            'package' => $package->getId(),
        ]);
    }

    /**
     * @Route("/update-all", name="update_all")
     */
    public function updateAllAction(): Response
    {
        $this->packageSynchronization->syncAll();

        $this->flashBag->add('success', 'Repositories updated');

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}", name="view", requirements={"package"="\d+"})
     * @Route("/{package}/{slug}", name="view_slug", requirements={"package"="\d+"})
     * @Route("/{package}/view", name="view_2", requirements={"package"="\d+"})
     */
    public function viewAction(Request $request, Package $package): Response
    {
        if (! $package->getVersions()->count()) {
            return $this->redirectToRoute('devliver_package_update', [
                'package' => $package->getId(),
            ]);
        }

        if ($request->get('_route') === 'devliver_package_view') {
            return $this->redirectToRoute('devliver_package_view_slug', [
                'package' => $package->getId(),
                'slug'    => $this->slugify->slugify($package->getName()),
            ]);
        }

        $packages = $package->getPackages();
        $stable   = $package->getLastStablePackage();

        return $this->render('package/view.html.twig', [
            'package'  => $package,
            'info'     => $stable,
            'versions' => $packages,
        ]);
    }
}
