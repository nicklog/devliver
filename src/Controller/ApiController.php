<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Entity\UpdateQueue;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function assert;
use function count;
use function json_decode;
use function Symfony\Component\String\u;

/**
 * @Route("/api", name="devliver_api_")
 */
class ApiController
{
    protected ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/update-package", name="package_update", defaults={"_format" = "json"}, methods={"POST"})
     */
    public function updatePackageAction(Request $request): Response
    {
        // parse the payload
        $payload = json_decode($request->request->get('payload'), true);

        if (! $payload && $request->headers->get('Content-Type') === 'application/json') {
            $payload = json_decode($request->getContent(), true);
        }

        if (! $payload) {
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

            $gitHttpUrl = $bitbucketHtmlUrl . '.git';
            $gitSshUrl  = 'git@bitbucket.org:' . u($bitbucketHtmlUrl)->replace('https://bitbucket.org/', '')->toString() . '.git';

            $urls[] = $gitHttpUrl;
            $urls[] = $gitSshUrl;
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
     * @param string[] $urls
     */
    protected function receiveWebhook(array $urls): Response
    {
        $packages = $this->findPackages($urls);

        if (count($packages) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Could not find a package that matches this request'], 404);
        }

        $em        = $this->registry->getManager();
        $queueRepo = $em->getRepository(UpdateQueue::class);

        foreach ($packages as $package) {
            assert($package instanceof Package);
            $updateQueue = $queueRepo->findOneByPackage($package);

            if ($updateQueue === null) {
                $updateQueue = new UpdateQueue();
                $updateQueue->setPackage($package);
            }

            $updateQueue->setLastCalledAt(new DateTime());

            $package->setAutoUpdate(true);
            $em->persist($package);
            $em->persist($updateQueue);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 202);
    }

    /**
     * @param string[] $urls
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
