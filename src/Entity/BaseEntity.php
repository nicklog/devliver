<?php

namespace Shapecode\Devliver\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseEntity
 *
 * @package Shapecode\Devliver\Entity
 * @author  Nikita Loges
 *
 * @ORM\MappedSuperclass()
 */
class BaseEntity implements BaseEntityInterface
{

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimeutc")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimeutc")
     */
    protected $updatedAt;

    /**
     *
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): \DateTime
    {

        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;

    }
}
