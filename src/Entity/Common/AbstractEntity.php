<?php

declare(strict_types=1);

namespace App\Entity\Common;

use App\Infrastructure\Error\InvalidArgumentTypeError;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

use function get_debug_type;

abstract class AbstractEntity implements Entity
{
    use Modified;

    /**
     * @ORM\Column(name="id", type="integer", options={"unsigned": true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    public function __construct()
    {
        $this->created = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isPersisted(): bool
    {
        return $this->id !== null;
    }

    public function equals(self $self): bool
    {
        if (! $self instanceof static) {
            throw new InvalidArgumentTypeError(static::class, get_debug_type($self));
        }

        return $this->getId() === $self->getId();
    }
}
