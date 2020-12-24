<?php

declare(strict_types=1);

namespace App\Composer;

use Composer\Config;
use Composer\Json\JsonFile;
use Symfony\Component\Filesystem\Filesystem;

use function file_put_contents;
use function getcwd;

class ConfigFactory
{
    protected string $composerDirectory;

    protected string $projectDir;

    public function __construct(
        string $composerDirectory,
        string $projectDir
    ) {
        $this->composerDirectory = $composerDirectory;
        $this->projectDir        = $projectDir;
    }

    public function create(): Config
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $fs = new Filesystem();
        if (! $fs->exists($this->getComposerConfigDir())) {
            $fs->mkdir($this->getComposerConfigDir());
        }

        $config = new Config(false, getcwd());
        $config->merge([
            'config'       => [
                'home' => $this->composerDirectory,
            ],
            'repositories' => [
                'packagist.org' => false,
            ],
        ]);

        $this->setConfigSource($config);
        $this->setAuthConfigSource($config);

        return $config;
    }

    protected function setConfigSource(Config $config): void
    {
        $filename = $this->getComposerConfigDir() . '/config.json';
        $file     = new JsonFile($filename);

        if (! $file->exists()) {
            file_put_contents($filename, '{}');
        }

        $source = new Config\JsonConfigSource($file);

        $config->merge($file->read());
        $config->setConfigSource($source);
    }

    protected function setAuthConfigSource(Config $config): void
    {
        $filename = $this->getComposerConfigDir() . '/auth.json';
        $file     = new JsonFile($filename);

        if ($file->exists()) {
            $config->merge(['config' => $file->read()]);
        } else {
            file_put_contents($filename, '{}');
        }

        $source = new Config\JsonConfigSource($file, true);

        $config->setAuthConfigSource($source);
    }

    protected function getComposerConfigDir(): string
    {
        return $this->projectDir . '/composer';
    }
}
