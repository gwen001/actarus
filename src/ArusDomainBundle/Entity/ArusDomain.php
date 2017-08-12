<?php

namespace ArusDomainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusProjectBundle\Entity\ArusProject;
use ArusHostBundle\Entity\ArusHost;

use Actarus\Utils;


/**
 * ArusDomain
 *
 * @ORM\Table(name="arus_domain", uniqueConstraints={@ORM\UniqueConstraint(columns={"project_id", "name"})})
 * @ORM\Entity(repositoryClass="ArusDomainBundle\Repository\ArusDomainRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusDomain
{
	const ENTITY_TYPE_ID = 3;

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
     * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="domains")
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="survey", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $survey;

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
	 * @ORM\OneToMany(targetEntity="ArusHostBundle\Entity\ArusHost", mappedBy="domain")
	 */
	private $hosts;

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
        $this->survey = 0;
        $this->status = 0;

        $this->hosts = new ArrayCollection();

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
	 * @return ArusDomain
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
	 * @return ArusDomain
	 */
	public function setProject(ArusProject $project)
	{
		$this->project = $project;
		$project->addDomain( $this );

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
     * @return ArusDomain
     */
    public function setName($name)
    {
        $this->name = strtolower($name);

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
     * Set survey
     *
     * @param string $survey
     *
     * @return ArusEntityAlert
     */
    public function setSurvey($survey)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return string
     */
    public function getSurvey()
    {
        return $this->survey;
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
     * @return ArusDomain
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
     * @return ArusDomain
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
	public function addHost(ArusHost $host) {
		$this->hosts[] = $host;
		return $this;
	}
	public function removeHost(ArusHost $host) {
		$this->hosts->removeElement( $host );
	}
	public function getHosts() {
		return $this->hosts;
	}
	public function setHosts($hosts) {
		$this->hosts = $hosts;
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
