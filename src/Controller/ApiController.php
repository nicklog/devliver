<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenolo\Utilities\Utils\StringUtil;

/**
 * Class ApiController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/api", name="devliver_api_")
 */
class ApiController extends Controller
{

    /**
     * @Route("/update-package", name="package_update", defaults={"_format" = "json"})
     * @Method({"POST"})
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
        if (isset($payload['repository']['ssh_url'])) {
            $urls[] = $payload['repository']['ssh_url'];
        }
        if (isset($payload['repository']['clone_url'])) {
            $urls[] = $payload['repository']['clone_url'];
        }

        // bitbucket
        if (isset($payload['repository']['links']['html']['href'])) {
            $bitbucketHtmlUrl = $payload['repository']['links']['html']['href'];

            $git_http_url = $bitbucketHtmlUrl . '.git';
            $git_ssh_url = 'git@bitbucket.org:' . StringUtil::removeFromStart('https://bitbucket.org/', $bitbucketHtmlUrl) . '.git';

            $urls[] = $git_http_url;
            $urls[] = $git_ssh_url;
        }

        if (empty($urls)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing or invalid payload'], 406);
        }

        return $this->receiveWebhook($request, $urls);
    }

    /**
     * Perform the package update
     *
     * @param Request $request
     * @param array   $urls
     *
     * @return Response
     */
    protected function receiveWebhook(Request $request, array $urls): Response
    {
        // find the user
        $user = $this->findUser($request);

        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid credentials'], 403);
        }

        $packages = $this->findPackages($urls);

        if (count($packages) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Could not find a package that matches this request'], 404);
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Package $package */
        foreach ($packages as $package) {
            $this->get('devliver.package_synchronization')->sync($package);

            $package->setAutoUpdate(true);
            $em->persist($package);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success'], 202);
    }

    /**
     * @param Request $request
     *
     * @return null|User
     */
    protected function findUser(Request $request): ?User
    {
        $username = $request->get('username');
        $token = $request->get('token');

        if ($username === null || $token === null) {
            return null;
        }

        /** @var UserRepository $repo */
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findOneByUsernameAndToken($username, $token);

        if ($user && !$user->isEnabled()) {
            return null;
        }

        return $user;
    }

    /**
     * @param array $urls
     *
     * @return Package[]
     */
    protected function findPackages(array $urls): array
    {
        $repo = $this->getDoctrine()->getRepository(Package::class);

        return $repo->findBy([
            'url' => $urls
        ]);
    }
}
