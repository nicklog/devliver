<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class FlashBagHelper
{
    private FlashBagInterface $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public function success(string $message): void
    {
        $this->flashBag->add('success', $message);
    }

    public function warning(string $message): void
    {
        $this->flashBag->add('warning', $message);
    }

    public function alert(string $message): void
    {
        $this->flashBag->add('danger', $message);
    }

    public function info(string $message): void
    {
        $this->flashBag->add('info', $message);
    }

    public function add(string $type, string $message): void
    {
        $this->flashBag->add($type, $message);
    }

    public function set(string $type, string $message): void
    {
        $this->flashBag->set($type, $message);
    }
}
