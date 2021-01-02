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
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;

use function assert;

/**
 * @Route("/packages", name="app_package_")
 */
final class PackageController extends AbstractController
{
    private PackageValidator $packageValidator;

    private ManagerRegistry $registry;

    private PaginatorInterface $paginator;

    private FormFactoryInterface $formFactory;

    private FlashBagInterface $flashBag;

    private ComposerManager $composerManager;

    private RepositoryHelper $repositoryHelper;

    private PackageSynchronization $packageSynchronization;

    public function __construct(
        PackageValidator $packageValidator,
        ManagerRegistry $registry,
        PaginatorInterface $paginator,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        ComposerManager $composerManager,
        RepositoryHelper $repositoryHelper,
        PackageSynchronization $packageSynchronization
    ) {
        $this->packageValidator       = $packageValidator;
        $this->registry               = $registry;
        $this->paginator              = $paginator;
        $this->formFactory            = $formFactory;
        $this->flashBag               = $flashBag;
        $this->composerManager        = $composerManager;
        $this->repositoryHelper       = $repositoryHelper;
        $this->packageSynchronization = $packageSynchronization;
    }

    /**
     * @Route("", name="index")
     */
    public function index(Request $request): Response
    {
        $repository = $this->registry->getRepository(Package::class);
        $qb         = $repository->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);

        $sort      = $request->query->get('sort', 'p.name');
        $direction = $request->query->get('direction', 'asc');
        $filter    = $request->query->get('filter');

        $qb->orderBy($sort, $direction);

        if ($filter !== null) {
            $qb->orWhere($qb->expr()->like('p.name', ':filter'));
            $qb->orWhere($qb->expr()->like('p.readme', ':filter'));
            $qb->orWhere($qb->expr()->like('p.url', ':filter'));
            $qb->orWhere($qb->expr()->like('p.replacementPackage', ':filter'));
            $qb->setParameter('filter', '%' . $filter . '%');
        }

        $pagination = $this->paginator->paginate($qb, $page);

        return $this->render('package/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{package}/abandon", name="abandon")
     */
    public function abandon(Request $request, Package $package): Response
    {
        $form = $this->formFactory->create(PackageAbandonType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

            $package->setAbandoned(true);
            $em->persist($package);
            $em->flush();

            $this->flashBag->add('success', 'Package abandoned');

            return $this->redirectToRoute('app_package_index');
        }

        return $this->render('package/abandon.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{package}/unabandon", name="unabandon")
     */
    public function unabandon(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setAbandoned(false);
        $package->setReplacementPackage(null);

        $em->persist($package);
        $em->flush();

        $this->flashBag->add('success', 'Package unabandoned');

        if ($request->headers->get('referer') !== null) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/{package}/enable", name="enable")
     */
    public function enable(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setEnable(true);

        $em->persist($package);
        $em->flush();

        if ($request->headers->get('referer') !== null) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/{package}/disable", name="disable")
     */
    public function disable(Request $request, Package $package): Response
    {
        $em = $this->registry->getManager();

        $package->setEnable(false);

        $em->persist($package);
        $em->flush();

        if ($request->headers->get('referer') !== null) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request): Response
    {
        $form = $this->createForm(PackageType::class, null, [
            'constraints' => [
                new Callback([$this->packageValidator, 'validateRepository']),
                new Callback([$this->packageValidator, 'validateAddName']),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

            $package = $form->getData();
            assert($package instanceof Package);

            $repository = $this->composerManager->createRepository($package);
            $info       = $this->repositoryHelper->getComposerInformation($repository);

            if (! isset($info['name'])) {
                throw new RuntimeException('name is missing', 1608916971650);
            }

            $package->setName($info['name']);

            $em->persist($package);
            $em->flush();

            $this->packageSynchronization->sync($package);

            $this->flashBag->add('success', 'Package added');

            return $this->redirectToRoute('app_package_index');
        }

        return $this->render('package/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{package}/edit", name="edit")
     */
    public function edit(Request $request, Package $package): Response
    {
        $form = $this->createForm(PackageType::class, $package, [
            'constraints' => [
                new Callback([$this->packageValidator, 'validateRepository']),
                new Callback([
                    'callback' => [$this->packageValidator, 'validateEditName'],
                    'payload'  => $package,
                ]),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();

            $package->setAutoUpdate(false);

            $em->persist($package);
            $em->flush();

            $this->packageSynchronization->sync($package);

            $this->flashBag->add('success', 'Package updated');

            return $this->redirectToRoute('app_package_edit', [
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
    public function delete(Package $package): Response
    {
        $em = $this->registry->getManager();

        $em->remove($package);
        $em->flush();

        $this->flashBag->add('success', 'Package removed');

        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/{package}/update", name="update")
     */
    public function update(Request $request, Package $package): Response
    {
        $this->packageSynchronization->sync($package);

        $this->flashBag->add('success', 'Package updated');

        if ($request->headers->get('referer') !== null) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_package_view', [
            'package' => $package->getId(),
        ]);
    }

    /**
     * @Route("/update-all", name="update_all")
     */
    public function updateAll(): Response
    {
        $this->packageSynchronization->syncAll();

        $this->flashBag->add('success', 'Repositories updated');

        return $this->redirectToRoute('app_package_index');
    }

    /**
     * @Route("/{package}", name="view", requirements={"package"="\d+"})
     */
    public function view(Request $request, Package $package): Response
    {
        if ($package->getVersions()->count() === 0) {
            return $this->redirectToRoute('app_package_update', [
                'package' => $package->getId(),
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
