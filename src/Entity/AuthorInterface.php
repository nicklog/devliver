<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface AuthorInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 * @company tenolo GbR
 */
interface AuthorInterface extends BaseEntityInterface
{

    /**
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void;

    /**
     * @return null|string
     */
    public function getEmail(): ?string;

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void;

    /**
     * @return null|string
     */
    public function getHomepage(): ?string;

    /**
     * @param null|string $homepage
     */
    public function setHomepage(?string $homepage): void;

    /**
     * @return null|string
     */
    public function getRole(): ?string;

    /**
     * @param null|string $role
     */
    public function setRole(?string $role): void;
}
