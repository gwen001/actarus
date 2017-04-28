<?php

namespace ArusRequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusProjectBundle\Entity\ArusProject;


/**
 * ArusRequest
 *
 * @ORM\Table(name="arus_request")
 * @ORM\Entity(repositoryClass="ArusRequestBundle\Repository\ArusRequestRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusRequest
{
	const ENTITY_TYPE_ID = 5;

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
	 * @ORM\Column(name="entity_id", type="string", length=64, unique=true)
	 */
	private $entityId;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="requests")
	 * @ORM\JoinColumn(nullable=false)
	 */
    private $project;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $name;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $url;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $host;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
	 */
	private $port;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=8, nullable=false)
	 */
	private $protocol;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=8, nullable=false)
	 */
	private $method;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $path;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $query;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $data;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $header;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $cookie;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="status", type="smallint", options={"unsigned"=true,"default":0})
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
	 * @return ArusRequest
	 */
	public function setName($name)
	{
		$this->name = $name;
		
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
	 * Set url
	 *
	 * @param string $url
	 *
	 * @return ArusRequest
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		
		return $this;
	}
	
	/**
	 * Get url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/**
	 * Set host
	 *
	 * @param string $host
	 *
	 * @return ArusRequest
	 */
	public function setHost($host)
	{
		$this->host = $host;
		
		return $this;
	}
	
	/**
	 * Get host
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}
	
	/**
	 * Set port
	 *
	 * @param string $port
	 *
	 * @return ArusRequest
	 */
	public function setPort($port)
	{
		$this->port = $port;
		
		return $this;
	}
	
	/**
	 * Get port
	 *
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}
	
	/**
	 * Set protocol
	 *
	 * @param string $protocol
	 *
	 * @return ArusRequest
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
		
		return $this;
	}
	
	/**
	 * Get protocol
	 *
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}
	
	/**
	 * Set method
	 *
	 * @param string $method
	 *
	 * @return ArusRequest
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		
		return $this;
	}
	
	/**
	 * Get method
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}
	
	/**
	 * Set path
	 *
	 * @param string $path
	 *
	 * @return ArusRequest
	 */
	public function setPath($path)
	{
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * Set query
	 *
	 * @param string $query
	 *
	 * @return ArusRequest
	 */
	public function setQuery($query)
	{
		$this->query = $query;
		
		return $this;
	}
	
	/**
	 * Get query
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}
	
	/**
	 * Set data
	 *
	 * @param string $data
	 *
	 * @return ArusRequest
	 */
	public function setData($data)
	{
		$this->data = $data;
		
		return $this;
	}
	
	/**
	 * Get data
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * Set header
	 *
	 * @param string $header
	 *
	 * @return ArusRequest
	 */
	public function setHeader($header)
	{
		$this->header = $header;
		
		return $this;
	}
	
	/**
	 * Get header
	 *
	 * @return string
	 */
	public function getHeader()
	{
		return $this->header;
	}
	
	/**
	 * Set cookie
	 *
	 * @param string $cookie
	 *
	 * @return ArusRequest
	 */
	public function setCookie($cookie)
	{
		$this->cookie = $cookie;
		
		return $this;
	}
	
	/**
	 * Get cookie
	 *
	 * @return string
	 */
	public function getCookie()
	{
		return $this->cookie;
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
     * @return ArusRequest
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
     * @return ArusRequest
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
			$project->addRequest( $this );
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
}
