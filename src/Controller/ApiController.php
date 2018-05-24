<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Package;
use Shapecode\Devliver\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 * @company tenolo GbR
 *
 * @Route("/", name="devliver_api_")
 */
class ApiController extends Controller
{

    /**
     * @Route("/api/update-package", name="generic_postreceive", defaults={"_format" = "json"})
     * @Method({"POST"})
     */
    public function updatePackageAction(Request $request)
    {
        // parse the payload
        $payload = json_decode($request->request->get('payload'), true);
        if (!$payload && $request->headers->get('Content-Type') === 'application/json') {
            $payload = json_decode($request->getContent(), true);
        }

        if (!$payload) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing payload parameter'], 406);
        }

        if (isset($payload['project']['git_http_url'])) { // gitlab event payload
            $urlRegex = '{^(?:ssh://git@|https?://|git://|git@)?(?P<host>[a-z0-9.-]+)(?::[0-9]+/|[:/])(?P<path>[\w.-]+(?:/[\w.-]+?)+)(?:\.git|/)?$}i';
            $url = $payload['project']['git_http_url'];
        } elseif (isset($payload['repository']['url'])) { // github/anything hook
            $urlRegex = '{^(?:ssh://git@|https?://|git://|git@)?(?P<host>[a-z0-9.-]+)(?::[0-9]+/|[:/])(?P<path>[\w.-]+(?:/[\w.-]+?)+)(?:\.git|/)?$}i';
            $url = $payload['repository']['url'];
            $url = str_replace('https://api.github.com/repos', 'https://github.com', $url);
        } elseif (isset($payload['repository']['links']['html']['href'])) { // bitbucket push event payload
            $urlRegex = '{^(?:https?://|git://|git@)?(?:api\.)?(?P<host>bitbucket\.org)[/:](?P<path>[\w.-]+/[\w.-]+?)(\.git)?/?$}i';
            $url = $payload['repository']['links']['html']['href'];
        } elseif (isset($payload['canon_url']) && isset($payload['repository']['absolute_url'])) { // bitbucket post hook (deprecated)
            $urlRegex = '{^(?:https?://|git://|git@)?(?P<host>bitbucket\.org)[/:](?P<path>[\w.-]+/[\w.-]+?)(\.git)?/?$}i';
            $url = $payload['canon_url'] . $payload['repository']['absolute_url'];
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing or invalid payload'], 406);
        }

        return $this->receivePost($request, $url, $urlRegex);
    }

    /**
     * Perform the package update
     *
     * @param Request $request  the current request
     * @param string  $url      the repository's URL (deducted from the request)
     * @param string  $urlRegex the regex used to split the user packages into domain and path
     *
     * @return Response
     */
    protected function receivePost(Request $request, $url, $urlRegex)
    {
        // try to parse the URL first to avoid the DB lookup on malformed requests
        if (!preg_match($urlRegex, $url)) {
            return new Response(json_encode(['status' => 'error', 'message' => 'Could not parse payload repository URL']), 406);
        }

        // find the user
        $user = $this->findUser($request);

        if (!$user) {
            return new Response(json_encode(['status' => 'error', 'message' => 'Invalid credentials']), 403);
        }

        // try to find the user package
        $packages = $this->findPackagesByUrl($url, $urlRegex);

        if (!$packages) {
            return new Response(json_encode(['status' => 'error', 'message' => 'Could not find a package that matches this request']), 404);
        }

        /** @var Package $package */
        foreach ($packages as $package) {
            $this->get('devliver.package_synchronization')->sync($package);
        }

        return new JsonResponse(['status' => 'success'], 202);
    }

    /**
     * @param Request $request
     *
     * @return null|User
     */
    protected function findUser(Request $request)
    {
        $username = $request->get('username');
        $apiToken = $request->get('apiToken');

        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findOneBy([
            'username' => $username,
            'apiToken' => $apiToken
        ]);

        if ($user && !$user->isEnabled()) {
            return null;
        }

        return $user;
    }

    /**
     * @param string $url
     * @param string $urlRegex
     *
     * @return array the packages found
     */
    protected function findPackagesByUrl($url, $urlRegex)
    {
        if (!preg_match($urlRegex, $url, $matched)) {
            return [];
        }

        $repo = $this->getDoctrine()->getRepository(Package::class);

        /** @var Package[] $all */
        $all = $repo->findAll();

        $packages = [];
        foreach ($all as $package) {
            foreach ($package->getRepos() as $r) {

                if (preg_match($urlRegex, $r->getUrl(), $candidate)
                    && strtolower($candidate['host']) === strtolower($matched['host'])
                    && strtolower($candidate['path']) === strtolower($matched['path'])
                ) {
                    $packages[] = $package;
                }
            }
        }

        return $packages;
    }
}
