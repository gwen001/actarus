<?php

namespace ArusServerServiceBundle\Entity;


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
	 * @var ArusServer
	 */
	private $server;
	
	/**
	 * @var int
	 */
	private $port;
    
	/**
	 * @var string
	 */
	private $type;
    
	/**
	 * @var string
	 */
	private $service;
    
	/**
	 * @var string
	 */
	private $version;
    
	
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
	 * Set server
	 *
	 * @param ArusServer $server
	 *
	 * @return Search
	 */
	public function setServer($server)
	{
		$this->server = $server;
		
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
	
	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return Search
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
	 * @return Search
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
	 * @return Search
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
     * Set port
     *
     * @param int $port
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
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}
