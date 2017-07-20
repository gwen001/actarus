<?php

namespace ArusServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusProjectBundle\Entity\ArusProject;
use ArusServerServiceBundle\Entity\ArusServerService;
use ArusHostServerBundle\Entity\ArusHostServer;

use Actarus\Utils;


/**
 * ArusServer
 *
 * @ORM\Table(name="arus_server", uniqueConstraints={@ORM\UniqueConstraint(columns={"project_id", "name"})})
 * @ORM\Entity(repositoryClass="ArusServerBundle\Repository\ArusServerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusServer
{
	const ENTITY_TYPE_ID = 2;

	public function getEntityType() {
		return self::ENTITY_TYPE_ID;
	}

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
	 * @ORM\Column(name="entity_id", type="string", length=64, unique=true)
	 */
	private $entityId;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="servers")
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16)
     */
    private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="alias", type="string", length=255, unique=false, nullable=true)
	 */
	private $alias;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="status", type="smallint", options={"unsigned"=true,"default":0})
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
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusServerServiceBundle\Entity\ArusServerService", mappedBy="server")
	 */
	private $services;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusHostServerBundle\Entity\ArusHostServer", mappedBy="server")
	 */
	private $hostservers;

	/**
	 * @var ArrayCollection
	 */
	private $entityAlerts;

	/**
	 * @var ArrayCollection
	 */
	private $entityTasks;

	/**
	 * @var ArrayCollection
	 */
	private $entityTechnologies;

	/**
	 * @var ArrayCollection
	 */
	private $entityAttachments;


	/*****************************************************/
	/* special functions                                 */
	/*****************************************************/
	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->status = 0;

		$this->hostservers = new ArrayCollection();

		$this->entityAlerts       = new ArrayCollection();
		$this->entityTasks        = new ArrayCollection();
		$this->entityTechnologies = new ArrayCollection();
		$this->entityAttachments = new ArrayCollection();
	}

	/**
	 * lifecycle prePersist
	 *
	 * @ORM\PrePersist
	 */
	public function prePersist()
	{
		$this->entityId  = uniqid( self::ENTITY_TYPE_ID );
		$this->createdAt = new \DateTime();
		$this->updatedAt = $this->createdAt;

		if( $this->alias == $this->name ) {
			$this->alias = '';
		}
	}

	/**
	 * lifecycle preUpdate
	 *
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();

		if( $this->alias == $this->name ) {
			$this->alias = '';
		}
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
	 * Get type
	 *
	 * @return int
	 */
	public function getType()
	{
		return self::ENTITY_TYPE_ID;
	}

	/**
	 * Set entityId
	 *
	 * @param string $entityId
	 *
	 * @return ArusHost
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
	 * Set project
	 *
	 * @param ArusProject $project
	 *
	 * @return ArusServer
	 */
	public function setProject(ArusProject $project)
	{
		$this->project = $project;
		$project->addServer( $this );

		return $this;
	}

	/**
	 * Get project
	 *
	 * @return ArusProject
	 */
	public function getProject()
	{
		return $this->project;
	}

	/**
     * Set name
     *
     * @param string $name
     *
     * @return ArusServer
     */
    public function setName( $name, $auto_alias=false )
    {
        $this->name = $name;

        if( $auto_alias && ($alias=gethostbyaddr($name)) != $name ) {
			$this->setAlias( $alias );
		}

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return ArusServer
     */
    public function setAlias($alias)
    {
    	if( $alias != $this->name ) {
	        $this->alias = $alias;
    	}

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
		return $this->alias;
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
     * @return ArusServer
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
     * @return ArusServer
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
	public function addHostServer(ArusHostServer $hostserver) {
		$this->hostservers[] = $hostserver;
		return $this;
	}
	public function removeHostServer(ArusHostServer $hostserver) {
		$this->hostservers->removeElement( $hostserver );
	}
	public function getHostServers() {
		return $this->hostservers;
	}
	public function setHostServers($hostservers) {
		$this->hostservers = $hostservers;
		return $this;
	}

	public function addService(ArusServerService $service) {
		$this->services[] = $service;
		return $this;
	}
	public function removeService(ArusServerService $service) {
		$this->services->removeElement( $service );
	}
	public function getServices() {
		return $this->services;
	}
	public function setServices($services) {
		$this->services = $services;
		return $this;
	}



	/*****************************************************/
	/* related entity objects                            */
	/*****************************************************/
	public function getEntityAlerts() {
		return $this->entityAlerts;
	}
	public function setEntityAlerts($alerts) {
		$this->entityAlerts = $alerts;
		return $this;
	}

	public function getEntityTasks() {
		return $this->entityTasks;
	}
	public function setEntityTasks($tasks) {
		$this->entityTasks = $tasks;
		return $this;
	}

	public function getEntityTechnologies() {
		return $this->entityTechnologies;
	}
	public function setEntityTechnologies($technologies) {
		$this->entityTechnologies = $technologies;
		return $this;
	}

	public function getEntityAttachments() {
		return $this->entityAttachments;
	}
	public function setEntityAttachments($attachments) {
		$this->entityAttachments = $attachments;
		return $this;
	}


	/*****************************************************/
	/* cunstom functions                                 */
	/*****************************************************/
	/**
	 * @var array
	 */
	private $score = null;

	public function getScore()
	{
		if( is_null($this->score) ) {
			$this->score = Utils::getScore( $this->entityAlerts );
		}

		return $this->score;
	}

	/**
	 * @var int
	 */
	private $maxAlertLevel = null;

	public function getMaxAlertLevel()
	{
		if( is_null($this->maxAlertLevel) ) {
			$this->maxAlertLevel = Utils::getMaxAlertLevel( $this->entityAlerts );
		}

		return $this->maxAlertLevel;
	}


	private $recon = true;

	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}

	public function getRecon() {
		return $this->recon;
	}
}
