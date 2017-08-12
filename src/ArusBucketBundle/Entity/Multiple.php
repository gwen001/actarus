<?php

namespace ArusBucketBundle\Entity;

use ArusProjectBundle\Entity\ArusProject;


class Multiple
{
	/**
	 * @var ArusProject
	 */
	private $project;

	/**
	 * @var string
	 */
	private $names;

	/**
	 * @var boolean
	 */
	private $recon = true;


	/**
	 * Set
	 *
	 * @param ArusProject $project
	 *
	 * @return Multiple
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
	 * Set names
	 *
	 * @param string $names
	 *
	 * @return Multiple
	 */
	public function setNames($names)
	{
		$this->names = $names;

		return $this;
	}

	/**
	 * Get names
	 *
	 * @return string
	 */
	public function getNames()
	{
		return $this->names;
	}


	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}
	public function getRecon() {
		return $this->recon;
	}
}
