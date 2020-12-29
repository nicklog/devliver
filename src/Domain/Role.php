<?php

declare(strict_types=1);

namespace App\Domain;

use MyCLabs\Enum\Enum;

/**
 * @method static self ADMIN()
 */
final class Role extends Enum
{
    public const ADMIN = 'ROLE_ADMIN';
}
