<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Form\Type\Forms\PackageAbandonType;
use Shapecode\Devliver\Form\Type\Forms\PackageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Class RepoController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/package", name="devliver_package_")
 */
class PackageController extends Controller
{

    /**
     * @Route("s", name="index")
     * @Template()
     *
     * @param Request $request
     *
     * @return Response|array
     */
    public function listAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Package::class);
        $qb = $repository->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $sort = $request->query->get('sort', 'p.name');
        $direction = $request->query->get('direction', 'asc');
        $filter = $request->query->get('filter');

        $qb->orderBy($sort, $direction);

        if (!empty($filter)) {
            $qb->orWhere($qb->expr()->like('p.name', ':filter'));
            $qb->orWhere($qb->expr()->like('p.readme', ':filter'));
            $qb->orWhere($qb->expr()->like('p.url', ':filter'));
            $qb->orWhere($qb->expr()->like('p.replacementPackage', ':filter'));
            $qb->setParameter('filter', '%' . $filter . '%');
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb, $page, $limit);

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * @Route("/{package}/abandon", name="abandon")
     * @Template()
     *
     * @param Request $request
     * @param Package $package
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function abandonAction(Request $request, Package $package)
    {
        $form = $this->createForm(PackageAbandonType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $package->setAbandoned(true);
            $em->persist($package);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Package abandoned');

            return $this->redirectToRoute('devliver_package_index');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{package}/unabandon", name="unabandon")
     *
     * @param Request $request
     * @param Package $package
     */
    public function unabandonAction(Request $request, Package $package)
    {
        $em = $this->getDoctrine()->getManager();

        $package->setAbandoned(false);
        $package->setReplacementPackage(null);

        $em->persist($package);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Package unabandoned');

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}/enable", name="enable")
     *
     * @param Request $request
     * @param Package $package
     */
    public function enableAction(Request $request, Package $package)
    {
        $em = $this->getDoctrine()->getManager();

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
     *
     * @param Request $request
     * @param Package $package
     */
    public function disableAction(Request $request, Package $package)
    {
        $em = $this->getDoctrine()->getManager();

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
     * @Route("/{package}/view", name="view", requirements={"package"="\d+"})
     * @Route("/{package}/{slug}", name="view_slug", requirements={"package"="\d+"})
     * @Template()
     *
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|array
     */
    public function viewAction(Request $request, Package $package)
    {
        if (!$package->getVersions()->count()) {
            return $this->redirectToRoute('devliver_package_update', [
                'package' => $package->getId()
            ]);
        }

        if ($request->get('_route') === 'devliver_package_view') {
            return $this->redirectToRoute('devliver_package_view_slug', [
                'package' => $package->getId(),
                'slug'    => $this->get('cocur_slugify_custom')->slugify($package->getName())
            ]);
        }

        $packages = $package->getPackages();
        $stable = $package->getLastStablePackage();

        return [
            'package'  => $package,
            'info'     => $stable,
            'versions' => $packages
        ];
    }

    /**
     * @Route("/add", name="add")
     * @Template()
     *
     * @param Request $request
     *
     * @return Response|array
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(PackageType::class, null, [
            'constraints' => [
                new Callback([$this->get('devliver.form_validator.package'), 'validateRepository']),
                new Callback([$this->get('devliver.form_validator.package'), 'validateAddName']),
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $url = $data['url'];
            $type = $data['type'];

            $repository = $this->get('devliver.composer_manager')->createRepositoryByUrl($url, $type);
            $info = $this->get('devliver.repository_helper')->getComposerInformation($repository);

            $package = new Package();
            $package->setUrl($url);
            $package->setType($type);
            $package->setName($info['name']);

            $em->persist($package);
            $em->flush();

            $this->get('devliver.package_synchronization')->sync($package);

            $this->get('session')->getFlashBag()->add('success', 'Package added');

            return $this->redirectToRoute('devliver_package_index');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{package}", name="edit")
     * @Template()
     *
     * @param Request $request
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|array
     */
    public function editAction(Request $request, Package $package)
    {
        $form = $this->createForm(PackageType::class, $package->getConfig(), [
            'constraints' => [
                new Callback([$this->get('devliver.form_validator.package'), 'validateRepository']),
                new Callback([
                    'callback' => [$this->get('devliver.form_validator.package'), 'validateEditName'],
                    'payload'  => ['package' => $package]
                ]),
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $url = $data['url'];
            $type = $data['type'];

            if ($url !== $package->getUrl() || $type !== $package->getType()) {
                $package->setUrl($url);
                $package->setType($type);
                $package->setAutoUpdate(false);

                $em->persist($package);
                $em->flush();

                $this->get('devliver.package_synchronization')->sync($package);
            }

            $this->get('session')->getFlashBag()->add('success', 'Package updated');

            return $this->redirectToRoute('devliver_package_edit', [
                'package' => $package->getId()
            ]);
        }

        return [
            'form'    => $form->createView(),
            'package' => $package
        ];
    }

    /**
     * @Route("/{package}/delete", name="delete")
     *
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Package $package)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($package);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Package removed');

        return $this->redirectToRoute('devliver_package_index');
    }

    /**
     * @Route("/{package}/update", name="update")
     *
     * @param Request $request
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Package $package)
    {
        $this->get('devliver.package_synchronization')->sync($package);

        $this->get('session')->getFlashBag()->add('success', 'Package updated');

        if ($request->headers->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_view', [
            'package' => $package->getId()
        ]);
    }

    /**
     * @Route("/update-all", name="update_all")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAllAction()
    {
        $this->get('devliver.repository_synchronization')->sync();

        $this->get('session')->getFlashBag()->add('success', 'Repositories updated');

        return $this->redirectToRoute('devliver_package_index');
    }
}
