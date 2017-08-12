<?php

namespace ArusServerBundle\Entity;

use ArusProjectBundle\Entity\ArusProject;


class Range
{
	/**
	 * @var ArusProject
	 */
	private $project;

	/**
	 * @var string
	 */
	private $range_start;

	/**
	 * @var string
	 */
	private $range_end;

	/**
	 * @var boolean
	 */
	private $recon = true;


	/**
	 * Set
	 *
	 * @param ArusProject $project
	 *
	 * @return Range
	 */
	public function setProject(ArusProject $project)
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
	 * Set range_start
	 *
	 * @param string $range_start
	 *
	 * @return Range
	 */
	public function setRangeStart($range_start)
	{
		$this->range_start = $range_start;

		return $this;
	}

	/**
	 * Get range_start
	 *
	 * @return string
	 */
	public function getRangeStart()
	{
		return $this->range_start;
	}

	
	/**
	 * Set range_end
	 *
	 * @param string $range_end
	 *
	 * @return Range
	 */
	public function setRangeEnd($range_end)
	{
		$this->range_end = $range_end;

		return $this;
	}

	/**
	 * Get range_end
	 *
	 * @return string
	 */
	public function getRangeEnd()
	{
		return $this->range_end;
	}


	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}
	public function getRecon() {
		return $this->recon;
	}
}
