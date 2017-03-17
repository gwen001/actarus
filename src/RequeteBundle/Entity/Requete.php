<?php

namespace RequeteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ArusProjectBundle\Entity\ArusProject;


/**
 * Requete
 *
 * @ORM\Table(name="requete")
 * @ORM\Entity(repositoryClass="RequeteBundle\Repository\RequeteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Requete
{
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="requetes")
	 * @ORM\JoinColumn(nullable=false)
	 */
    private $project;
	
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
	
	
	/*****************************************************/
	/* special functions                                 */
	/*****************************************************/
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
	 * Set project
	 *
	 * @param ArusProject $project
	 *
	 * @return Requete
	 */
	public function setProject(ArusProject $project)
	{
		$this->project = $project;
		$project->addRequete( $this );
		
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
	 * Set url
	 *
	 * @param string $url
	 *
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
	 * @return Requete
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Requete
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
     * @return Requete
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
}
