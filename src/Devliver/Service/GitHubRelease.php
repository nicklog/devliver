<?php

namespace Shapecode\Devliver\Service;

use Github\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Filesystem\Filesystem;

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

    /** @var string */
    protected $versionFile;

    /** @var array */
    protected $tags;

    /**
     * @param Client            $client
     * @param AdapterInterface  $cache
     * @param                   $versionFile
     */
    public function __construct(Client $client, AdapterInterface $cache, $versionFile)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->versionFile = $versionFile;
    }

    /**
     * @return mixed
     */
    public function getVersionData()
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->versionFile)) {
            $tag = $this->getLastTag();
            $this->setVersionData($tag);
        } else {
            $content = file_get_contents($this->versionFile);
            $tag = json_decode($content, true);
        }

        return $tag;
    }

    /**
     * @param $tag
     */
    public function setVersionData($tag)
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->versionFile, json_encode($tag));
    }

    /**
     * @return array
     */
    public function getCurrentTag()
    {
        $current = $this->getVersionData();

        foreach ($this->getAllTags() as $release) {
            if ($release['name'] == $current['name']) {
                return $release;
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getLastTag()
    {
        return $this->getAllTags()[0];
    }

    /**
     * @return mixed
     */
    public function getAllTags()
    {
        if (!is_null($this->tags)) {
            return $this->tags;
        }

        $cachedReleases = $this->cache->getItem('devliver_tags');

        if ($cachedReleases->isHit()) {
            $tags = $cachedReleases->get();
        } else {
            $tags = $this->client->api('repo')->tags('shapecode', 'devliver');

            $cachedReleases->set($tags);
            $cachedReleases->expiresAfter(3600);

            $this->cache->save($cachedReleases);
        }

        $this->tags = $tags;

        return $tags;
    }

    /**
     * @param $tagName
     *
     * @return null
     */
    public function getTagByTagName($tagName)
    {
        foreach ($this->getAllTags() as $tag) {
            if ($tag['name'] == $tagName) {
                return $tag;
            }
        }

        return null;
    }
}
