<?php

namespace ArusHostBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusProjectBundle\Entity\ArusProject;
use ArusDomainBundle\Entity\ArusDomain;
use ArusHostBundle\Entity\ArusHost;
use ArusHostServerBundle\Entity\ArusHostServer;

use Actarus\Utils;


/**
 * ArusHost
 *
 * @ORM\Table(name="arus_host", uniqueConstraints={@ORM\UniqueConstraint(columns={"project_id", "name"})})
 * @ORM\Entity(repositoryClass="ArusHostBundle\Repository\ArusHostRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusHost
{
	const ENTITY_TYPE_ID = 4;

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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="status", type="smallint", options={"unsigned"=true,"default":0})
	 */
	private $status;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="hosts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $project;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusDomainBundle\Entity\ArusDomain", inversedBy="hosts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $domain;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusHostServerBundle\Entity\ArusHostServer", cascade={"persist","remove"}, mappedBy="host")
	 */
	private $hostservers;

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
	 */
	private $entityAlerts;

	/**
	 * @var ArrayCollection
	 */
	private $entityComments;

	/**
	 * @var ArrayCollection
	 */
	private $entityLoots;

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
		$this->entityComments     = new ArrayCollection();
		$this->entityLoots        = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return ArusHost
     */
    public function setName( $name )
    {
        $this->name = strtolower( $name );

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
	 * @return ArusHost
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
	 * @return ArusHost
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
	 * @return ArusHost
	 */
	public function setProject($project)
	{
		$this->project = $project;
		if( is_object($project) ) {
			$project->addHost( $this );
		}

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
	 * Set domain
	 *
	 * @param ArusDomain $domain
	 *
	 * @return ArusHost
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
		if( is_object($domain) ) {
			$domain->addHost($this);
		}

		return $this;
	}

	/**
	 * Get domain
	 *
	 * @return ArusDomain
	 */
	public function getDomain()
	{
		return $this->domain;
	}


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

	public function getEntityComments() {
		return $this->entityComments;
	}
	public function setEntityComments($comments) {
		$this->entityComments = $comments;
		return $this;
	}

	public function getEntityLoots() {
		return $this->entityLoots;
	}
	public function setEntityLoots($loots) {
		$this->entityLoots = $loots;
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
