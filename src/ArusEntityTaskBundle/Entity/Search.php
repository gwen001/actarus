<?php

namespace ArusEntityTaskBundle\Entity;


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
	 * @var int
	 */
	private $entityType;

	/**
	 * @var string
	 */
	private $entityId;

	/**
	 * @var string
	 */
	private $command;

	/**
	 * @var string
	 */
	private $output;

	/**
	 * @var int
	 */
	private $clusterId;

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
	 * Set entityType
	 *
	 * @param string $entityType
	 *
	 * @return Search
	 */
	public function setEntityType($entityType)
	{
		$this->entityType = $entityType;

		return $this;
	}

	/**
	 * Get entityType
	 *
	 * @return string
	 */
	public function getEntityType()
	{
		return $this->entityType;
	}

	/**
	 * Set entityId
	 *
	 * @param string $entityId
	 *
	 * @return Search
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
	 * Set command
	 *
	 * @param string $command
	 *
	 * @return Search
	 */
	public function setCommand($command)
	{
		$this->command = $command;

		return $this;
	}

	/**
	 * Get command
	 *
	 * @return string
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * Set output
	 *
	 * @param string $output
	 *
	 * @return Search
	 */
	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * Get output
	 *
	 * @return string
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * Set clusterId
	 *
	 * @param string $clusterId
	 *
	 * @return Search
	 */
	public function setClusterId($clusterId)
	{
		$this->clusterId = $clusterId;

		return $this;
	}

	/**
	 * Get clusterId
	 *
	 * @return int
	 */
	public function getClusterId()
	{
		return $this->clusterId;
	}

	/**
	 * Set status
	 *
	 * @param string $status
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
