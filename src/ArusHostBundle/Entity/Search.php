<?php

namespace ArusHostBundle\Entity;


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
	//private $server;

	/**
	 * @var ArusDomain
	 */
	private $domain;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $status = null;

    /**
     * @var string
     */
    private $ip;

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
	 * Set server
	 *
	 * @param ArusServer $server
	 *
	 * @return Search
	 */
//	public function setServer($server)
//	{
//		$this->server = $server;
//
//		return $this;
//	}

	/**
	 * Get server
	 *
	 * @return ArusServer
	 */
//	public function getServer()
//	{
//		return $this->server;
//	}

	/**
	 * Set domain
	 *
	 * @param ArusDomain $domain
	 *
	 * @return Search
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;

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

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Search
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
	 * Set status
	 *
	 * @param int $status
	 *
	 * @return Search
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Search
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
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
