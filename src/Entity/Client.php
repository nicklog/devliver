<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client extends AbstractEntity implements UserInterface
{
    /** @ORM\Column(type="string", unique=true) */
    private string $name;

    /** @ORM\Column(type="string") */
    private string $password;

    /** @ORM\Column(type="string", unique=true) */
    private string $token;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    private bool $enable = true;

    public function __construct(
        string $name
    ) {
        parent::__construct();

        $this->name     = $name;
        $this->token    = Uuid::uuid4()->toString();
        $this->password = $this->token;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [
            'ROLE_USER',
            'ROLE_REPO',
        ];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->getName();
    }

    public function eraseCredentials(): void
    {
    }
}
