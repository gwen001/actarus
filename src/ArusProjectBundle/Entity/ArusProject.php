<?php

namespace ArusProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusBucketBundle\Entity\ArusBucket;
use ArusServerBundle\Entity\ArusServer;
use ArusDomainBundle\Entity\ArusDomain;
use ArusHostBundle\Entity\ArusHost;
use ArusRequestBundle\Entity\ArusRequest;
use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityTaskBundle\Entity\ArusEntityTask;

use Actarus\Utils;


/**
 * ArusProject
 *
 * @ORM\Table(name="arus_project")
 * @ORM\Entity(repositoryClass="ArusProjectBundle\Repository\ArusProjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusProject
{
	const ENTITY_TYPE_ID = 1;

	public function getEntityType() {
		return self::ENTITY_TYPE_ID;
	}

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="entity_id", type="string", length=64, nullable=false, unique=true)
	 */
	private $entityId;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255, unique=true)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="handle", type="string", length=255, unique=true, nullable=false)
	 */
	private $handle;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false, options={"unsigned"=true,"default":0})
     */
    private $status;

    /**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created_at", type="datetime", nullable=false)
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updated_at", type="datetime", nullable=false)
	 */
	private $updatedAt;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusBucketBundle\Entity\ArusBucket", cascade={"remove"}, mappedBy="project")
	 */
	private $buckets;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusServerBundle\Entity\ArusServer", cascade={"remove"}, mappedBy="project")
	 */
	private $servers;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusDomainBundle\Entity\ArusDomain", cascade={"remove"}, mappedBy="project")
	 */
	private $domains;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusHostBundle\Entity\ArusHost", cascade={"persist","remove"}, mappedBy="project")
	 */
	private $hosts;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusRequestBundle\Entity\ArusRequest", cascade={"persist","remove"}, mappedBy="project")
	 */
	private $requests;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusEntityAlertBundle\Entity\ArusEntityAlert", cascade={"remove"}, mappedBy="project")
	 */
	private $alerts;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusEntityTaskBundle\Entity\ArusEntityTask", cascade={"remove"}, mappedBy="project")
	 */
	private $tasks;

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

        $this->buckets  = new ArrayCollection();
        $this->servers  = new ArrayCollection();
		$this->domains  = new ArrayCollection();
		$this->hosts    = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->alerts   = new ArrayCollection();
		$this->tasks    = new ArrayCollection();

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
	 * @return ArusProject
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
	 * @return ArusProject
	 */
	public function setName( $name, $set_handle=false )
	{
		$this->name = $name;
		//$this->name = strtolower( $name );
		$this->setHandle( preg_replace('#[^a-zA-Z0-9]#', '',strtolower($this->name)) );

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
	 * Set name
	 *
	 * @param string $handle
	 *
	 * @return ArusProject
	 */
	public function setHandle( $handle )
	{
		$this->handle = $handle;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getHandle()
	{
		return $this->handle;
	}

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ArusEntityAlert
     */
    public function setStatus( $status )
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
	 * @return ArusProject
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
	 * @return ArusProject
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
	public function addBucket(ArusBucket $bucket) {
		$this->buckets[] = $bucket;
		return $this;
	}
	public function removeBucket(ArusBucket $bucket) {
		$this->buckets->removeElement( $bucket );
	}
	public function getBuckets() {
		return $this->buckets;
	}
	public function setBuckets($buckets) {
		$this->buckets = $buckets;
		return $this;
	}


	public function addServer(ArusServer $server) {
		$this->servers[] = $server;
		return $this;
	}
	public function removeServer(ArusServer $server) {
		$this->servers->removeElement( $server );
	}
	public function getServers() {
		return $this->servers;
	}
	public function setServers($servers) {
		$this->servers = $servers;
		return $this;
	}


	public function addDomain(ArusDomain $domain) {
		$this->domains[] = $domain;
		return $this;
	}
	public function removeDomain(ArusDomain $domain) {
		$this->domains->removeElement( $domain );
	}
	public function getDomains() {
		return $this->domains;
	}
	public function setDomains($domains) {
		$this->domains = $domains;
		return $this;
	}


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


	public function addRequest(ArusRequest $request) {
		$this->requests[] = $request;
		return $this;
	}
	public function removeRequest(ArusRequest $request) {
		$this->requests->removeElement( $request );
	}
	public function getRequests() {
		return $this->requests;
	}
	public function setRequests($requests) {
		$this->requests = $requests;
		return $this;
	}


	public function addAlert(ArusEntityAlert $alert) {
		$this->alerts[] = $alert;
		return $this;
	}
	public function removeAlert(ArusEntityAlert $alert) {
		$this->alerts->removeElement( $alert );
	}
	public function getAlerts() {
		return $this->alerts;
	}
	public function setAlerts($alerts) {
		$this->alerts = $alerts;
		return $this;
	}


	public function addTask(ArusEntityTask $task) {
		$this->tasks[] = $task;
		return $this;
	}
	public function removeTask(ArusEntityTask $task) {
		$this->tasks->removeElement( $task );
	}
	public function getTasks() {
		return $this->tasks;
	}
	public function setTasks($tasks) {
		$this->tasks = $tasks;
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
	private $totalScore = null;

	public function getTotalScore()
	{
		if( is_null($this->totalScore) ) {
			$this->totalScore = Utils::getScore( $this->alerts );
		}

		return $this->totalScore;
	}

	/**
	 * @var int
	 */
	private $totalMaxAlertLevel = null;

	public function getTotalMaxAlertLevel()
	{
		if( is_null($this->totalMaxAlertLevel) ) {
			$this->totalMaxAlertLevel = Utils::getMaxAlertLevel( $this->alerts );
		}

		return $this->totalMaxAlertLevel;
	}


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


	/**
	 * @var boolean $recon true to perform the recon
	 */
	private $recon = true;

	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}

	public function getRecon() {
		return $this->recon;
	}
}
