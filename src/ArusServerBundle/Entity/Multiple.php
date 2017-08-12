<?php

namespace ArusServerBundle\Entity;

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
	private $ips;

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
	 * Set ips
	 *
	 * @param string $ips
	 *
	 * @return Multiple
	 */
	public function setIps($ips)
	{
		$this->ips = $ips;

		return $this;
	}

	/**
	 * Get ips
	 *
	 * @return string
	 */
	public function getIps()
	{
		return $this->ips;
	}


	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}
	public function getRecon() {
		return $this->recon;
	}
}
