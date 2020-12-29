<?php

declare(strict_types=1);

namespace App\Security\Checker;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function assert;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        assert($user instanceof User);

//        if (! $user->isAccountActive()) {
//            throw new DisabledException();
//        }
//
//        if ($user->isAccountLocked()) {
//            throw new LockedException();
//        }
//
//        if ($user->isAccountExpired()) {
//            throw new AccountExpiredException();
//        }
    }
}
