<?php

namespace ArusServerServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ArusServerBundle\Entity\ArusServer;


/**
 * ArusServerService
 *
 * @ORM\Table(name="arus_server_service", uniqueConstraints={@ORM\UniqueConstraint(columns={"server_id", "port"})})
 * @ORM\Entity(repositoryClass="ArusServerServiceBundle\Repository\ArusServerServiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusServerService
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
     * @ORM\ManyToOne(targetEntity="ArusServerBundle\Entity\ArusServer", inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $server;

    /**
     * @var string
     *
     * @ORM\Column(name="port", type="integer", nullable=false, unique=false)
     */
    private $port;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=8, nullable=false, unique=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="service", type="string", length=255, nullable=true, unique=false)
     */
    private $service;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=255, nullable=true, unique=false)
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
     * Set port
     *
     * @param integer $port
     *
     * @return ArusServerService
     */
    public function setPort($port)
    {
        $this->port = (int)$port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ArusEntityAlert
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
	
    /**
     * Set service
     *
     * @param string $service
     *
     * @return ArusEntityAlert
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }
	
    /**
     * Set version
     *
     * @param string $version
     *
     * @return ArusEntityAlert
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
     * @return ArusServerService
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
     * @return ArusServerService
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
	public function setServer(ArusServer $server)
	{
		$this->server = $server;
		$server->addService( $this );

		return $this;
	}
	public function getServer()
	{
		return $this->server;
	}
}
