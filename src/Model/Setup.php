<?php

namespace Shapecode\Devliver\Model;

/**
 * Class Setup
 *
 * @package Shapecode\Devliver\Model
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class Setup
{

    /** @var SetupDatabase|null */
    protected $database;

    /**
     * @return Setup
     */
    public static function create(): Setup
    {
        $setup = new static();
        $setup->setDatabase(SetupDatabase::create());

        return $setup;
    }

    /**
     * @return SetupDatabase|null
     */
    public function getDatabase(): ?SetupDatabase
    {
        return $this->database;
    }

    /**
     * @param SetupDatabase|null $database
     */
    public function setDatabase(?SetupDatabase $database): void
    {
        $this->database = $database;
    }
}
