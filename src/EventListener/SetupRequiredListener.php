<?php

namespace Shapecode\Devliver\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SetupRequiredListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class SetupRequiredListener implements EventSubscriberInterface
{

    /** @var RouterInterface */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->get('_route') === null || $request->get('_route') === 'devliver_setup_index') {
            return;
        }

        $requiredEnvs = [
            'DATABASE_URL',
        ];

        foreach ($requiredEnvs as $env) {
            if (getenv($env) === false) {
                $event->setResponse(new RedirectResponse($this->router->generate('devliver_setup_index')));
                $event->stopPropagation();

                return;
            }
        }
    }

}
