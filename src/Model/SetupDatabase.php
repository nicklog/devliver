<?php

namespace Shapecode\Devliver\Model;

/**
 * Class SetupDatabase
 *
 * @package Shapecode\Devliver\Model
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class SetupDatabase
{

    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $password;

    /** @var string|null */
    protected $host = 'localhost';

    /** @var int|null */
    protected $port = 3306;

    /** @var string|null */
    protected $name = 'devliver';

    /**
     * @return SetupDatabase
     */
    public static function create(): SetupDatabase
    {
        return new static();
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     */
    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
