<?php

namespace Shapecode\Devliver\Command;

use Avris\Dotenv\Dotenv;
use Shapecode\Devliver\Service\SetupHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function GuzzleHttp\Psr7\build_query;

/**
 * Class SetupCommand
 *
 * @package Shapecode\Devliver\Command
 * @author  Nikita Loges
 */
class SetupCommand extends Command
{

    /** @var SetupHelper */
    protected $setupHelper;

    /** @var string */
    protected $projectDir;

    /** @var array */
    protected $vars = [];

    /**
     * @param SetupHelper $setupHelper
     * @param string      $projectDir
     */
    public function __construct(
        SetupHelper $setupHelper,
        string $projectDir
    ) {
        parent::__construct();

        $this->setupHelper = $setupHelper;
        $this->projectDir = $projectDir;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('devliver:setup');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->setupHelper->isNeeded()) {
            return;
        }

        $style = new SymfonyStyle($input, $output);

        $envFile = $this->projectDir.'/.env.local';
        $envFileDist = $this->projectDir.'/.env.local.dist';
        $fs = new Filesystem();
        $dotenv = new Dotenv();

        if ($fs->exists($envFile)) {
            $this->vars = $dotenv->read($envFile);
        } else {
            $this->vars = $dotenv->read($envFileDist);
        }

        if ($this->setupHelper->isGeneralSetupNeeded()) {
            $this->setupGeneral($style);
        }

        if ($this->setupHelper->isDatabaseSetupNeeded()) {
            $this->setupDatabase($style);
        }

        if ($this->setupHelper->isMailerSetupNeeded()) {
            $this->setupMailer($style);
        }

        $dotenv->save($envFile, $this->vars);
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function setupGeneral(SymfonyStyle $io): void
    {
        $io->section('General setup');

        $locale = $io->ask('Locale', $_SERVER['LOCALE'] ?? 'en');
        $host = $io->ask('Under which domain do devliver run?', $_SERVER['DEVLIVER_HOST'] ?? null);
        $scheme = $io->confirm('Do you use the https protocol?', true);
        $baseUrl = $io->ask('Do you run Devliver in a subdirectory?', $_SERVER['DEVLIVER_BASE_URL']);

        if (getenv('APP_SECRET') === false) {
            $secret = $this->getRandomToken();
            $this->vars['app']['APP_SECRET'] = $secret;
        }

        $this->vars['app']['LOCALE'] = $locale;
        $this->vars['app']['DEVLIVER_HOST'] = $host;
        $this->vars['app']['DEVLIVER_BASE_URL'] = $baseUrl;
        $this->vars['app']['DEVLIVER_SCHEME'] = $scheme ? 'https' : 'http';
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function setupDatabase(SymfonyStyle $io): void
    {
        $io->section('Setup for your MySQL database');

        $host = $io->ask('Hostname', 'localhost');
        $port = $io->ask('Port', 3306);
        $username = $io->ask('Username', 'devliver');
        $password = $io->ask('Password');
        $name = $io->ask('Database Name', 'devliver');

        if ($password !== null) {
            $password = ':'.$password;
        }

        $url = 'mysql://'.$username.$password.'@'.$host.':'.$port.'/'.$name;

        $this->vars['doctrine']['DATABASE_URL'] = $url;
    }

    /**
     * @param SymfonyStyle $io
     */
    protected function setupMailer(SymfonyStyle $io): void
    {
        $io->section('Setup for your mailer');

        $protocol = $io->choice('Select a type', [
            'null',
            'smtp',
            'gmail',
        ], 'smtp');

        if ($protocol !== 'null') {
            $host = $io->ask('Host');
            $username = $io->ask('Username');
            $password = $io->ask('Password');
            $encryption = $io->confirm('SSL Encryption?', true);
            $port = $io->ask('Port', $encryption ? 465 : 25);
            $authmode = $io->ask('Auth-Mode', 'login');
            $sender = $io->ask('Sender-Email-Address', $username);
        }

        if ($protocol === 'null') {
            $url = 'null://localhost';
            $sender = 'user@example.net';
        } else {
            $query = [
                'username'  => $username,
                'password'  => $password,
                'auth_mode' => $authmode,
            ];

            if ($encryption) {
                $query['encryption'] = 'ssl';
            }

            $url = $protocol.'://'.$host.':'.$port.'?'.urldecode(build_query($query));
        }

        $this->vars['mailer']['MAILER_URL'] = $url;
        $this->vars['mailer']['MAILER_SENDER'] = $sender;
    }

    protected function getRandomToken(int $length = 32)
    {
        if (!isset($length) || $length <= 8) {
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

}
