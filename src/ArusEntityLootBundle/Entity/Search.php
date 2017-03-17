<?php

namespace ArusEntityLootBundle\Entity;


/**
 * Search
 */
class Search
{
	/**
	 * @var int
	 */
    private $id;
	
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
	private $descr;
	
	/**
	 * @var \DateTime
	 */
	private $minCreatedAt;
	
	/**
	 * @var \DateTime
	 */
	private $maxCreatedAt;
	
	
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
	 * Set descr
	 *
	 * @param string $descr
	 *
	 * @return Search
	 */
	public function setDescr($descr)
	{
		$this->descr = $descr;
		
		return $this;
	}
	
	/**
	 * Get descr
	 *
	 * @return string
	 */
	public function getDescr()
	{
		return $this->descr;
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
