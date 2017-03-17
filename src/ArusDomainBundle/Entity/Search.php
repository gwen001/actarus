<?php

namespace ArusDomainBundle\Entity;


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
	private $name;
    
    /**
     * @var int
     */
    private $status = null;
    
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
