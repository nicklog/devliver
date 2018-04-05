<?php

namespace Shapecode\Devliver\Service;

use Github\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class GitHubRelease
 *
 * @package Shapecode\Devliver\Service
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class GitHubRelease
{

    /** @var Client */
    protected $client;

    /** @var AdapterInterface */
    protected $cache;

    /** @var */
    protected $currentRelease;

    /** @var array */
    protected $releases;

    /**
     * @param Client            $client
     * @param AdapterInterface  $cache
     * @param                   $currentRelease
     */
    public function __construct(Client $client, AdapterInterface $cache, $currentRelease)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->currentRelease = $currentRelease;
    }

    /**
     * @return array
     */
    public function getLatestRelease()
    {
        $releases = $this->getAllReleases();

        return $releases[0];
    }

    /**
     * @return array
     */
    public function getCurrentRelease()
    {
        foreach ($this->getAllReleases() as $release) {
            if ($release['name'] == $this->currentRelease) {
                return $release;
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getAllReleases()
    {
        if (!is_null($this->releases)) {
            return $this->releases;
        }

        $cachedReleases = $this->cache->getItem('devliver_releases');

        if ($cachedReleases->isHit()) {
            $releases = $cachedReleases->get();
        } else {
            $releases = $this->client->api('repo')->releases()->all('shapecode', 'devliver');

            $cachedReleases->set($releases);
            $cachedReleases->expiresAfter(3600);

            $this->cache->save($cachedReleases);
        }

        $this->releases = $releases;

        return $releases;
    }
}
