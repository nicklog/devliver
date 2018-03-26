<?php

namespace Shapecode\Devliver\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class FosUserListener
 *
 * @package Shapecode\Devliver\EventListener
 * @author  Nikita Loges
 */
class FosUserListener implements EventSubscriberInterface
{

    /** @var UrlGeneratorInterface */
    protected $router;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordCompleted',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onChangePasswordCompleted(FormEvent $event)
    {
        $url = $this->router->generate('devliver_index');
        $response = new RedirectResponse($url);

        $event->setResponse($response);
    }
}
