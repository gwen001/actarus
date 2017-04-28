<?php

namespace ArusRequestBundle\Entity;


/**
 * Search
 */
class Search
{
	/**
	 * @var int
	 */
    private $page;

	/**
	 * @var int
	 */
    private $id;
	
	/**
	 * @var ArusProject
	 */
	private $project;
	
	/**
	 * @var string
	 */
	private $url;
	
	/**
	 * @var string
	 */
	private $host;
	
	/**
	 * @var int
	 */
	private $port;
	
	/**
	 * @var string
	 */
	private $protocol;
	
	/**
	 * @var string
	 */
	private $method;
	
	/**
	 * @var string
	 */
	private $path;
	
	/**
	 * @var string
	 */
	private $query;
	
	/**
	 * @var string
	 */
	private $data;
	
	/**
	 * @var string
	 */
	private $header;
	
	/**
	 * @var string
	 */
	private $cookie;
	
	/**
	 * @var \DateTime
	 */
	private $minCreatedAt;
	
	/**
	 * @var \DateTime
	 */
	private $maxCreatedAt;
	
	
	public function __construct() {
		$this->page = 1;
	}

	
	/**
	 * Set page
	 *
	 * @param string $page
	 *
	 * @return Search
	 */
	public function setPage($page)
	{
		$this->page = $page;

		return $this;
	}

	/**
	 * Get page
	 *
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}

	
	/**
	 * Set id
	 *
	 * @param string $id
	 *
	 * @return Search
	 */
	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}
	
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
	 * @return Search
	 */
	public function setProject($project)
	{
		$this->project = $project;
		
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
     * Set minCreatedAt
     *
     * @param \DateTime $minCreatedAt
     *
     * @return Search
     */
    public function setMinCreatedAt($minCreatedAt)
    {
        $this->minCreatedAt = $minCreatedAt;

        return $this;
    }

    /**
     * Get minCreatedAt
     *
     * @return \DateTime
     */
    public function getMinCreatedAt()
    {
        return $this->minCreatedAt;
    }
	
	/**
     * Set maxCreatedAt
     *
     * @param \DateTime $maxCreatedAt
     *
     * @return Search
     */
    public function setMaxCreatedAt($maxCreatedAt)
    {
        $this->maxCreatedAt = $maxCreatedAt;

        return $this;
    }

    /**
     * Get maxCreatedAt
     *
     * @return \DateTime
     */
    public function getMaxCreatedAt()
    {
        return $this->maxCreatedAt;
    }
}
