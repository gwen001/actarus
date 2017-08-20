<?php

namespace ArusDomainBundle\Entity;

use ArusProjectBundle\Entity\ArusProject;


class Import
{
	/**
	 * @var ArusProject
	 */
	private $project;

	/**
	 * @var string
	 */
	private $source_file;

	/**
	 * @var boolean
	 */
	private $recon = true;


	/**
	 * Set
	 *
	 * @param ArusProject $project
	 *
	 * @return Import
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
	 * Set source_file
	 *
	 * @param string $source_file
	 *
	 * @return Import
	 */
	public function setSourcefile($source_file)
	{
		$this->source_file = $source_file;

		return $this;
	}

	/**
	 * Get source_file
	 *
	 * @return string
	 */
	public function getSourcefile()
	{
		return $this->source_file;
	}


	public function setRecon( $recon ) {
		$this->recon = (bool)$recon;
		return $this;
	}
	public function getRecon() {
		return $this->recon;
	}
}
