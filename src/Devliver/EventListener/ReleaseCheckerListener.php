<?php

namespace Shapecode\Devliver\EventListener;

use Shapecode\Devliver\Service\GitHubRelease;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ReleaseCheckerListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class ReleaseCheckerListener implements EventSubscriberInterface
{

    /** @var FlashBagInterface */
    protected $flashBag;

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var GitHubRelease */
    protected $github;

    /**
     * @param FlashBagInterface             $flashBag
     * @param AuthorizationCheckerInterface $authChecker
     * @param GitHubRelease                 $github
     */
    public function __construct(
        FlashBagInterface $flashBag,
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        GitHubRelease $github)
    {
        $this->flashBag = $flashBag;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->github = $github;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     *
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->tokenStorage->getToken()) {
            return;
        }

        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $latestRelease = $this->github->getLatestRelease();
        $currentRelease = $this->github->getCurrentRelease();

        if ($latestRelease['id'] != $currentRelease['id']) {
            $this->flashBag->add('info', '<a href="' . $latestRelease['html_url'] . '" class="alert-link" target="_blank">New version <b>' . $latestRelease['name'] . '</b> available</a>');
        }
    }
}
