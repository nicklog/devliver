<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Role;
use App\Entity\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

use function array_search;
use function array_unique;
use function implode;
use function in_array;
use function sprintf;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends AbstractEntity implements UserInterface
{
    /** @ORM\Column(type="string") */
    private string $email;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $password = null;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    private bool $enable = true;

    /**
     * @ORM\Column(type="json")
     *
     * @var string[]
     */
    private array $roles = [];

    public function __construct(
        string $email
    ) {
        parent::__construct();

        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
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
     * @inheritDoc
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function addRole(string | Role $role): void
    {
        $role = (string) $role;

        if (in_array($role, $this->roles, true)) {
            return;
        }

        if (! Role::isValid($role)) {
            throw new InvalidArgumentException(sprintf('%s given but $role has to be one of these values: %s', $role, implode(', ', Role::toArray())));
        }

        $this->roles[] = $role;
    }

    public function removeRole(string | Role $role): void
    {
        $role = (string) $role;

        if (! in_array($role, $this->roles, true)) {
            return;
        }

        unset($this->roles[array_search($role, $this->roles, true)]);
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
