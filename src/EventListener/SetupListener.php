<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SetupListener implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator   = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'setup',
        ];
    }

    public function setup(RequestEvent $event): void
    {
        if (! $event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->get('_route') === 'app_setup') {
            return;
        }

        if ($this->userRepository->countAll() > 0) {
            return;
        }

        $response = new RedirectResponse(
            $this->urlGenerator->generate('app_setup')
        );

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
