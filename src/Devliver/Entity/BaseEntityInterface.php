<?php

namespace Shapecode\Devliver\Entity;

/**
 * Interface BaseEntityInterface
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 */
interface BaseEntityInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime;
}
