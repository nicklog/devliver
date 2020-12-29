<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Entity\UpdateQueue;
use App\Repository\UpdateQueueRepository;
use Carbon\Carbon;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function count;
use function json_decode;
use function Symfony\Component\String\u;

use const JSON_THROW_ON_ERROR;

/**
 * @Route("/api", name="app_api_")
 */
final class ApiController extends AbstractController
{
    private ManagerRegistry $registry;

    private UpdateQueueRepository $updateQueueRepository;

    public function __construct(
        ManagerRegistry $registry,
        UpdateQueueRepository $updateQueueRepository,
    ) {
        $this->registry              = $registry;
        $this->updateQueueRepository = $updateQueueRepository;
    }

    /**
     * @Route("/update-package", name="package_update", defaults={"_format" = "json"}, methods={"POST"})
     */
    public function updatePackage(Request $request): Response
    {
        // parse the payload
        $payload = json_decode($request->request->get('payload'), true, 512, JSON_THROW_ON_ERROR);

        if ($payload === null && $request->headers->get('Content-Type') === 'application/json') {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        }

        if ($payload === null) {
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

        if (count($urls) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing or invalid payload'], 406);
        }

        return $this->receiveWebhook($urls);
    }

    /**
     * @param string[] $urls
     */
    private function receiveWebhook(array $urls): Response
    {
        $packages = $this->findPackages($urls);

        if (count($packages) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Could not find a package that matches this request'], 404);
        }

        $em = $this->registry->getManager();

        foreach ($packages as $package) {
            $updateQueue = $this->updateQueueRepository->findOneByPackage($package);

            if ($updateQueue === null) {
                $updateQueue = new UpdateQueue(
                    $package,
                    Carbon::now()
                );
            }

            $updateQueue->setLastCalledAt(Carbon::now());
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
    private function findPackages(array $urls): array
    {
        $repo = $this->registry->getRepository(Package::class);

        return $repo->findBy([
            'url' => $urls,
        ]);
    }
}
