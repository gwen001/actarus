<?php

namespace ArusEntityTechnologyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

use ArusTechnologyBundle\Entity\ArusTechnology;


/**
 * ArusEntityTechnology
 *
 * @ORM\Table(name="arus_entity_technology", uniqueConstraints={@ORM\UniqueConstraint(columns={"technology_id", "entity_id"})}, indexes={@Index(name="idx_entity_id", columns={"entity_id"})})
 * @ORM\Entity(repositoryClass="ArusEntityTechnologyBundle\Repository\ArusEntityTechnologyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusEntityTechnology
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
	 * @ORM\ManyToOne(targetEntity="ArusTechnologyBundle\Entity\ArusTechnology")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $technology;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="entity_id", type="string", length=64, nullable=false, unique=false)
	 */
	private $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16, nullable=true, unique=false)
     */
    private $version;

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
	 * @return ArusEntityTechnology
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
     * Set version
     *
     * @param string $version
     *
     * @return ArusEntityTechnology
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ArusEntityTechnology
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
     * @return ArusEntityTechnology
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

	public function getTechnology() {
		return $this->technology;
	}
	public function setTechnology( ArusTechnology $technology ) {
		$this->technology = $technology;
		return $this;
	}
}
