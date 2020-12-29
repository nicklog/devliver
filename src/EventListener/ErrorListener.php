<?php

declare(strict_types=1);

namespace App\EventListener;

use NunoMaduro\Collision\Writer;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Whoops\Exception\Inspector;

final class ErrorListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => 'onConsoleError',
        ];
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $error = $event->getError();

        if ($error instanceof ExceptionInterface) {
            return;
        }

        $writer = new Writer();
        $writer->setOutput($event->getOutput());
        $writer->write(new Inspector($error));

        $event->setExitCode(0);
    }
}
