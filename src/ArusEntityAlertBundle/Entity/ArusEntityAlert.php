<?php

namespace ArusEntityAlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ArusProjectBundle\Entity\ArusProject;
use ArusTaskBundle\Entity\Arus;


/**
 * ArusEntityAlert
 *
 * @ORM\Table(name="arus_entity_alert", indexes={@Index(name="idx_entity_id", columns={"entity_id"})})
 * @ORM\Entity(repositoryClass="ArusEntityAlertBundle\Repository\ArusEntityAlertRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusEntityAlert
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
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="alerts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $project;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="entity_id", type="string", length=64, nullable=false, unique=false)
	 */
	private $entityId;
	
	/**
     * @var string
     *
     * @ORM\Column(name="descr", type="text")
     */
    private $descr;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="level", type="smallint", nullable=false, options={"unsigned"=true})
	 */
	private $level;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="status", type="smallint", nullable=false, options={"unsigned"=true,"default":0})
	 */
	private $status;

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
		$this->status = 0;
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
     * @return ArusEntityAlert
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
     * Set descr
     *
     * @param string $descr
     *
     * @return ArusEntityAlert
     */
    public function setDescr($descr)
    {
        $this->descr = $descr;

        return $this;
    }

    /**
     * Get descr
     *
     * @return string
     */
    public function getDescr()
    {
        return $this->descr;
    }
	
	/**
	 * Set level
	 *
	 * @param string $level
	 *
	 * @return ArusEntityAlert
	 */
	public function setLevel($level)
	{
		$this->level = $level;
		
		return $this;
	}
	
	/**
	 * Get level
	 *
	 * @return string
	 */
	public function getLevel()
	{
		return $this->level;
	}
	
	/**
	 * Set status
	 *
	 * @param string $status
	 *
	 * @return ArusEntityAlert
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		
		return $this;
	}
	
	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ArusEntityAlert
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
     * @return ArusEntityAlert
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
	/**
	 * Set project
	 *
	 * @param ArusProject $project
	 *
	 * @return ArusEntityTask
	 */
	public function setProject(ArusProject $project) {
		$this->project = $project;
		return $this;
	}
	/**
	 * Get project
	 *
	 * @return ArusProject
	 */
	public function getProject() {
		return $this->project;
	}
	
	public function getEntity() {
		return $this->entity;
	}
	public function setEntity( $entity ) {
		$this->entity = $entity;
		return $this;
	}
    
    
    /*****************************************************/
    /* custom functions                                  */
    /*****************************************************/
    public function isNew() {
        return ($this->status == 0);
    }
    public function isConfirmed() {
        return ($this->status == 1);
    }
    public function isCancelled() {
        return ($this->status == 2);
    }
}
