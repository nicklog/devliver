<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Repo;
use Shapecode\Devliver\Form\Type\Forms\RepoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepoController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/repositories", name="devliver_repo_")
 */
class RepoController extends Controller
{

    /**
     * @Route("", name="index")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(Repo::class);
        $qb = $repo->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $sort = $request->query->get('sort', 'p.id');
        $direction = $request->query->get('direction', 'asc');
        $filter = $request->query->get('filter');

        $qb->orderBy($sort, $direction);

        if (!empty($filter)) {
            $qb->andWhere($qb->expr()->like('p.url', ':filter'));
            $qb->setParameter('filter', '%' . $filter . '%');
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb, $page, $limit);

        return $this->render('@Devliver/Repo/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/add", name="add")
     *
     * @param Request $request
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $repo = new Repo();
        $form = $this->createForm(RepoType::class, $repo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $repo->setCreator($this->getUser());

            $em->persist($repo);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Repositories added');

            return $this->redirectToRoute('devliver_repo_index');
        }

        return $this->render('@Devliver/Repo/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Repo::class);
        $repo = $repository->find($id);

        $form = $this->createForm(RepoType::class, $repo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            if ($repo->getCreator() === null) {
                $repo->setCreator($this->getUser());
            }

            $em->persist($repo);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Repository updated');

            return $this->redirectToRoute('devliver_repo_edit', [
                'id' => $repo->getId()
            ]);
        }

        return $this->render('@Devliver/Repo/edit.html.twig', [
            'form' => $form->createView(),
            'repo' => $repo
        ]);
    }

    /**
     * @Route("/{id}/update", name="update")
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Repo::class);
        $repo = $repository->find($id);

        if (!$repo) {
            return $this->redirectToRoute('devliver_repo_index');
        }

        $this->get('devliver.repository_synchronization')->syncRepo($repo);

        $this->get('session')->getFlashBag()->add('success', 'Repository updated');

        if ($request->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_repo_view', [
            'id' => $repo->getId()
        ]);
    }

    /**
     * @Route("/{repo}/abandon", name="abandon")
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function abandonAction(Request $request, $id)
    {
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

        return $this->redirectToRoute('devliver_repo_index');
    }

    /**
     * @Route("/{id}/delete", name="delete")
     *
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository(Repo::class);
        $repo = $repository->find($id);

        if (!$repo) {
            return $this->redirectToRoute('devliver_repo_index');
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($repo);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Repository removed');

        return $this->redirectToRoute('devliver_repo_index');
    }
}
