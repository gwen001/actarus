<?php

namespace SqlmapBundle\Entity;


class Sqlmap
{
	/**
	 * @var int
	 */
	private $delay;
	
	
	/**
	 * Set delay
	 *
	 * @param int $delay
	 *
	 * @return Settings
	 */
	public function setDelay($delay)
	{
		$this->delay = $delay;
		
		return $this;
	}
	
	/**
	 * Get delay
	 *
	 * @return int
	 */
	public function getDelay()
	{
		return $this->delay;
	}
}
