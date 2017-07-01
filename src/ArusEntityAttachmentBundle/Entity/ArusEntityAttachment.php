<?php

namespace ArusEntityAttachmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ArusProjectBundle\Entity\ArusProject;


/**
 * ArusEntityAttachment
 *
 * @ORM\Table(name="arus_entity_attachment", indexes={@Index(name="idx_entity_id", columns={"entity_id"})})
 * @ORM\Entity(repositoryClass="ArusEntityAttachmentBundle\Repository\ArusEntityAttachmentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusEntityAttachment
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
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="attachments")
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
	 * @ORM\Column(name="filename", type="string", length=64, nullable=false, unique=true)
	 */
	private $filename;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="realname", type="string", length=255, nullable=false, unique=false)
	 */
	private $realname;
	
	/**
     * @var string
     *
	 * @ORM\Column(name="title", type="string", length=255, unique=false)
     */
    private $title;
	
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
	public function __construct() {
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
     * @return ArusEntityAttachment
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
	 * Set filename
	 *
	 * @param string $filename
	 *
	 * @return ArusEntityAttachment
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
		
		return $this;
	}
	
	/**
	 * Get filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	
	/**
	 * Set realname
	 *
	 * @param string $realname
	 *
	 * @return ArusEntityAttachment
	 */
	public function setRealname($realname)
	{
		$this->realname = $realname;
		
		return $this;
	}
	
	/**
	 * Get realname
	 *
	 * @return string
	 */
	public function getRealname()
	{
		return $this->realname;
	}

    /**
     * Set title
     *
     * @param string $title
     *
     * @return ArusEntityAttachment
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
	
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ArusEntityAttachment
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
     * @return ArusEntityAttachment
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
}
