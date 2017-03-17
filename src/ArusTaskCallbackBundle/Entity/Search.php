<?php

namespace ArusTaskCallbackBundle\Entity;


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
	 * @var string
	 */
	private $taskId;
	
	
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
	 * Set taskId
	 *
	 * @param string $taskId
	 *
	 * @return Search
	 */
	public function setTaskId($taskId)
	{
		$this->taskId = $taskId;
		
		return $this;
	}
	
	/**
	 * Get taskId
	 *
	 * @return string
	 */
	public function getTaskId()
	{
		return $this->taskId;
	}
}
