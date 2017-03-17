<?php

namespace ArusEntityTechnologyBundle\Entity;


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
	private $entityType;
	
	/**
	 * @var string
	 */
	private $entityId;
	
	/**
	 * @var string
	 */
	private $technology;
	
	/**
	 * @var int
	 */
	private $technologyId;
	
	
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
	 * Set technology
	 *
	 * @param string $technology
	 *
	 * @return Search
	 */
	public function setTechnology($technology)
	{
		$this->technology = $technology;
		
		return $this;
	}
	
	/**
	 * Get technology
	 *
	 * @return string
	 */
	public function getTechnology()
	{
		return $this->technology;
	}
	
	/**
	 * Set technologyId
	 *
	 * @param int $technologyId
	 *
	 * @return Search
	 */
	public function setTechnologyId($technologyId)
	{
		$this->technologyId = $technologyId;
		
		return $this;
	}
	
	/**
	 * Get technologyId
	 *
	 * @return int
	 */
	public function getTechnologyId()
	{
		return $this->technologyId;
	}
}
