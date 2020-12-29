<?php

declare(strict_types=1);

namespace App\Composer;

use Composer\Config;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

use function getcwd;
use function putenv;
use function sprintf;

final class ConfigFactory
{
    private string $composerDirectory;

    public function __construct(
        string $composerDirectory,
    ) {
        $this->composerDirectory = $composerDirectory;
    }

    public function create(): Config
    {
        putenv(sprintf('COMPOSER_HOME=%s', $this->composerDirectory));

        unset(Config::$defaultRepositories['packagist.org']);

        $fs = new Filesystem();
        if (! $fs->exists($this->composerDirectory)) {
            $fs->mkdir($this->composerDirectory);
        }

        $cwd = getcwd();

        if ($cwd === false) {
            throw new RuntimeException('cwd() returned false', 1608917718640);
        }

        $config = new Config(false, $cwd);
        $config->merge([
            'config'       => [
                'home' => $this->composerDirectory,
            ],
            'repositories' => [
                'packagist.org' => false,
            ],
        ]);

        return $config;
    }
}
