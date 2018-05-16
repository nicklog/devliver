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

    /**
     * @param string $composerDirectory
     */
    public function __construct(string $composerDirectory)
    {
        $this->composerDirectory = $composerDirectory;
    }

    /**
     * @return Config
     */
    public function create(): Config
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $fs = new Filesystem();
        if (!$fs->exists($this->composerDirectory)) {
            $fs->mkdir($this->composerDirectory);
        }

        $config = new Config(false, getcwd());

        $this->setConfigSource($config);
        $this->setAuthConfigSource($config);

        return $config;
    }

    /**
     * @param Config $config
     */
    protected function setConfigSource(Config $config)
    {
        $file = new JsonFile($this->composerDirectory . '/config.json');

        $source = new Config\JsonConfigSource($file);
        $source->addConfigSetting('home', $this->composerDirectory);

        $config->merge($file->read());
        $config->setConfigSource(new Config\JsonConfigSource($file));
    }

    /**
     * @param Config $config
     */
    protected function setAuthConfigSource(Config $config)
    {
        $file = new JsonFile($this->composerDirectory . '/auth.json');
        if ($file->exists()) {
            $config->merge(['config' => $file->read()]);
        }
        $config->setAuthConfigSource(new Config\JsonConfigSource($file, true));
    }
}
