<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UpdateQueue;
use App\Repository\PackageRepository;
use App\Repository\UpdateQueueRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

use function array_merge;
use function array_unique;
use function count;
use function is_array;
use function is_string;
use function json_decode;
use function Symfony\Component\String\u;

use const JSON_THROW_ON_ERROR;

/**
 * @Route("/api", name="app_api_")
 */
final class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private UpdateQueueRepository $updateQueueRepository;

    private PackageRepository $packageRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UpdateQueueRepository $updateQueueRepository,
        PackageRepository $packageRepository
    ) {
        $this->entityManager         = $entityManager;
        $this->updateQueueRepository = $updateQueueRepository;
        $this->packageRepository     = $packageRepository;
    }

    /**
     * @Route("/update-package", name="package_update", defaults={"_format" = "json"}, methods={"POST"})
     */
    public function updatePackage(Request $request): Response
    {
        // parse the payload
        try {
            $payload = json_decode($request->request->get('payload'), true, 512, JSON_THROW_ON_ERROR);
        } catch (TypeError $exception) {
            $payload = null;
        }

        if ($payload === null && $request->headers->get('Content-Type') === 'application/json') {
            try {
                $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            } catch (TypeError $exception) {
                $payload = null;
            }
        }

        if ($payload === null) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => 'Missing payload parameter',
            ], 406);
        }

        $places = [
            'project'    => [
                'git_ssh_url', // gitlab
                'git_http_url', // gitlab
                'ssh_url', // gitlab
                'http_url', // gitlab
            ],
            'repository' => [
                'url', // gogs
                'git_url', // github / gitea
                'clone_url', // github / gitea
                'ssh_url', // github
            ],
        ];

        $urls = $this->findUrls($payload, $places);

        // bitbucket
        if (isset($payload['repository']['links']['html']['href'])) {
            $bitbucketHtmlUrl = $payload['repository']['links']['html']['href'];

            $gitHttpUrl = $bitbucketHtmlUrl . '.git';
            $gitSshUrl  = 'git@bitbucket.org:' . u($bitbucketHtmlUrl)->replace('https://bitbucket.org/', '')->toString() . '.git';

            $urls[] = $gitHttpUrl;
            $urls[] = $gitSshUrl;
        }

        if (count($urls) === 0) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => 'Missing or invalid payload',
            ], 406);
        }

        return $this->receiveWebhook($urls);
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $places
     *
     * @return string[]
     */
    private function findUrls(array $payload, array $places): array
    {
        $urls = [];

        foreach ($places as $key => $value) {
            if (is_string($key) && is_array($value)) {
                if (isset($payload[$key]) && is_array($payload[$key])) {
                    $urls[] = $this->findUrls(
                        $payload[$key],
                        $value
                    );
                }
            } elseif (is_string($value) && isset($payload[$value])) {
                $urls[] = [$payload[$value]];
            }
        }

        return array_unique(array_merge(...$urls));
    }

    /**
     * @param string[] $urls
     */
    private function receiveWebhook(array $urls): Response
    {
        $packages = $this->packageRepository->findBy([
            'url' => $urls,
        ]);

        if (count($packages) === 0) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => 'Could not find a package that matches this request',
            ], 404);
        }

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

            $this->entityManager->persist($package);
            $this->entityManager->persist($updateQueue);
        }

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success'], 202);
    }
}
