<?php

namespace ArusEntityCommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ArusEntityCommentBundle\Entity\ArusEntityComment;


/**
 * ArusEntityComment
 *
 * @ORM\Table(name="arus_entity_comment", indexes={@Index(name="idx_entity_id", columns={"entity_id"})})
 * @ORM\Entity(repositoryClass="ArusEntityCommentBundle\Repository\ArusEntityCommentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusEntityComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="entity_id", type="string", length=64, nullable=false, unique=false)
	 */
	private $entityId;
	
	/**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
	
	/**
	 * @var mixed
	 */
	private $entity;
	
	
	/*****************************************************/
	/* special functions                                 */
	/*****************************************************/
	/**
	 * Construct
	 */
	public function __construct()
	{
	}
	
	/**
	 * lifecycle prePersist
	 *
	 * @ORM\PrePersist
	 */
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = $this->createdAt;
	}
	
	/**
	 * lifecycle preUpdate
	 *
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}
	
	
	/*****************************************************/
	/* setter and getter                                 */
	/*****************************************************/
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entityId
     *
     * @param string $entityId
     *
     * @return ArusEntityComment
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
	
    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return ArusEntityComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ArusEntityComment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ArusEntityComment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

	
	/*****************************************************/
	/* related objects                                   */
	/*****************************************************/
	public function getEntity() {
		return $this->entity;
	}
	public function setEntity( $entity ) {
		$this->entity = $entity;
		return $this;
	}
}
