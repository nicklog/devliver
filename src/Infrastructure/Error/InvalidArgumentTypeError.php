<?php

declare(strict_types=1);

namespace App\Infrastructure\Error;

use InvalidArgumentException;
use Throwable;

use function sprintf;

final class InvalidArgumentTypeError extends InvalidArgumentException
{
    public function __construct(
        string $expectedType,
        string $actualType,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = sprintf('Expected argument of type "%s", "%s" given', $expectedType, $actualType);

        parent::__construct($message, $code, $previous);
    }
}
