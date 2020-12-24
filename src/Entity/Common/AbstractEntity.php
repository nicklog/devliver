<?php

declare(strict_types=1);

namespace App\Entity\Common;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

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
}
