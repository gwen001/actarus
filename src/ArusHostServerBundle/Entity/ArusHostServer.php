<?php

namespace ArusHostServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ArusHostBundle\Entity\ArusHost;
use ArusServerBundle\Entity\ArusServer;


/**
 * ArusHostServer
 *
 * @ORM\Table(name="arus_host_server", uniqueConstraints={@ORM\UniqueConstraint(columns={"host_id", "server_id"})})
 * @ORM\Entity(repositoryClass="ArusHostServerBundle\Repository\ArusHostServerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusHostServer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusHostBundle\Entity\ArusHost", inversedBy="hostservers")
	 * @ORM\JoinColumn(name="host_id", referencedColumnName="id", onDelete="CASCADE")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $host;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusServerBundle\Entity\ArusServer", inversedBy="hostservers")
	 * @ORM\JoinColumn(name="server_id", referencedColumnName="id", onDelete="CASCADE")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $server;

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
	 * Set host
	 *
	 * @param ArusHost $host
	 *
	 * @return ArusHost
	 */
	public function setHost($host)
	{
		$this->host = $host;
//		if( is_object($host) ) {
//			$host->addHost( $this );
//		}

		return $this;
	}

	/**
	 * Get host
	 *
	 * @return ArusHost
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Set server
	 *
	 * @param ArusServer $server
	 *
	 * @return ArusHost
	 */
	public function setServer($server)
	{
		$this->server = $server;
//		if( is_object($server) ) {
//			$server->addHost( $this );
//		}

		return $this;
	}

	/**
	 * Get server
	 *
	 * @return ArusServer
	 */
	public function getServer()
	{
		return $this->server;
	}
}
