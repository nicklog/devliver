<?php

namespace Shapecode\Devliver\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\UpdateQueue;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tenolo\Utilities\Utils\StringUtil;

/**
 * Class ApiController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/api", name="devliver_api_")
 */
class ApiController
{

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/update-package", name="package_update", defaults={"_format" = "json"}, methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updatePackageAction(Request $request): Response
    {
        // parse the payload
        $payload = json_decode($request->request->get('payload'), true);

        if (!$payload && $request->headers->get('Content-Type') === 'application/json') {
            $payload = json_decode($request->getContent(), true);
        }

        if (!$payload) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing payload parameter'], 406);
        }

        $urls = [];

        // gitlab
        if (isset($payload['project']['git_ssh_url'])) {
            $urls[] = $payload['project']['git_ssh_url'];
        }
        if (isset($payload['project']['git_http_url'])) {
            $urls[] = $payload['project']['git_http_url'];
        }

        // github
        if (isset($payload['repository']['git_url'])) {
            $urls[] = $payload['repository']['git_url'];
        }
        if (isset($payload['repository']['clone_url'])) {
            $urls[] = $payload['repository']['clone_url'];
        }

        // bitbucket
        if (isset($payload['repository']['links']['html']['href'])) {
            $bitbucketHtmlUrl = $payload['repository']['links']['html']['href'];

            $git_http_url = $bitbucketHtmlUrl.'.git';
            $git_ssh_url = 'git@bitbucket.org:'.StringUtil::removeFromStart('https://bitbucket.org/', $bitbucketHtmlUrl).'.git';

            $urls[] = $git_http_url;
            $urls[] = $git_ssh_url;
        }

        // gogs
        if (isset($payload['repository']['url'])) {
            $urls[] = $payload['repository']['url'];
        }

        // github/gitea
        if (isset($payload['repository']['ssh_url'])) {
            $urls[] = $payload['repository']['ssh_url'];
        }

        // gitea
        if (isset($payload['repository']['clone_url'])) {
            $urls[] = $payload['repository']['clone_url'];
        }

        if (empty($urls)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing or invalid payload'], 406);
        }

        return $this->receiveWebhook($urls);
    }

    /**
     * @param Request $request
     * @param array   $urls
     *
     * @return Response
     */
    protected function receiveWebhook(array $urls): Response
    {
        $packages = $this->findPackages($urls);

        if (count($packages) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Could not find a package that matches this request'], 404);
        }

        $em = $this->registry->getManager();
        $queueRepo = $em->getRepository(UpdateQueue::class);

        /** @var Package $package */
        foreach ($packages as $package) {
            $updateQueue = $queueRepo->findOneByPackage($package);

            if ($updateQueue === null) {
                $updateQueue = new UpdateQueue();
                $updateQueue->setPackage($package);
            }

            $updateQueue->setLastCalledAt(new \DateTime());

            $package->setAutoUpdate(true);
            $em->persist($package);
            $em->persist($updateQueue);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 202);
    }

    /**
     * @param array $urls
     *
     * @return Package[]
     */
    protected function findPackages(array $urls): array
    {
        $repo = $this->registry->getRepository(Package::class);

        return $repo->findBy([
            'url' => $urls,
        ]);
    }
}
