<?php

namespace Shapecode\Devliver\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ComposerListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class ComposerListener implements EventSubscriberInterface
{

    /** @var string */
    protected $homeDirectory;

    /** @var string */
    protected $cacheDirectory;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => [
                ['onKernelRequest', 1000],
            ],
            ConsoleEvents::COMMAND => [
                ['onCommand', 1000],
            ]
        ];
    }

    /**
     * @param $homeDirectory
     * @param $cacheDirectory
     */
    public function __construct($homeDirectory, $cacheDirectory)
    {
        $this->homeDirectory = $homeDirectory;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->setEnvironmentVariables();
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->setEnvironmentVariables();
    }

    /**
     *
     */
    protected function setEnvironmentVariables()
    {
        putenv('COMPOSER_HOME=' . $this->homeDirectory);
        putenv('COMPOSER_CACHE_DIR=' . $this->cacheDirectory);
    }
}
