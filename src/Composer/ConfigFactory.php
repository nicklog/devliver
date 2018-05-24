<?php

namespace Shapecode\Devliver\Composer;

use Composer\Config;
use Composer\Json\JsonFile;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ConfigFactory
 *
 * @package Shapecode\Devliver\Composer
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class ConfigFactory
{

    /** @var string */
    protected $composerDirectory;

    /** @var string */
    protected $projectDir;

    /**
     * @param string $composerDirectory
     * @param string $projectDir
     */
    public function __construct(string $composerDirectory, string $projectDir)
    {
        $this->composerDirectory = $composerDirectory;
        $this->projectDir = $projectDir;
    }

    /**
     * @return Config
     */
    public function create(): Config
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $fs = new Filesystem();
        if (!$fs->exists($this->getComposerConfigDir())) {
            $fs->mkdir($this->getComposerConfigDir());
        }

        $config = new Config(false, getcwd());
        $config->merge([
            'config'       => [
                'home' => $this->composerDirectory,
            ],
            'repositories' => [
                'packagist.org' => false
            ]
        ]);

        $this->setConfigSource($config);
        $this->setAuthConfigSource($config);

        return $config;
    }

    /**
     * @param Config $config
     */
    protected function setConfigSource(Config $config)
    {
        $filename = $this->getComposerConfigDir() . '/config.json';
        $file = new JsonFile($filename);

        if (!$file->exists()) {
            file_put_contents($filename, '{}');
        }

        $source = new Config\JsonConfigSource($file);

        $config->merge($file->read());
        $config->setConfigSource($source);
    }

    /**
     * @param Config $config
     */
    protected function setAuthConfigSource(Config $config)
    {
        $filename = $this->getComposerConfigDir() . '/auth.json';
        $file = new JsonFile($filename);

        if ($file->exists()) {
            $config->merge(['config' => $file->read()]);
        } else {
            file_put_contents($filename, '{}');
        }

        $source = new Config\JsonConfigSource($file, true);

        $config->setAuthConfigSource($source);
    }

    /**
     * @return string
     */
    protected function getComposerConfigDir()
    {
        return $this->projectDir . '/composer';
    }
}
