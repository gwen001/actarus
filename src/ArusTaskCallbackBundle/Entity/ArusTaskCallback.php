<?php

namespace ArusTaskCallbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

use ArusTaskBundle\Entity\ArusTask;


/**
 * ArusTaskCallback
 *
 * @ORM\Table(name="arus_task_callback")
 * @ORM\Entity(repositoryClass="ArusTaskCallbackBundle\Repository\ArusTaskCallbackRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusTaskCallback
{
    /**
     * @var int
     *
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusTaskBundle\Entity\ArusTask", inversedBy="callbacks")
	 * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $task;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="priority", type="smallint", nullable=false, options={"unsigned"=true})
	 */
	private $priority;

	/**
     * @var string
     *
     * @ORM\Column(name="regex", type="string", length=255, nullable=false)
     */
    private $regex;

	/**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=32, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="object")
     */
    private $params;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created_at", type="datetime", nullable=false)
	 */
	private $createdAt;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="updated_at", type="datetime", nullable=false)
	 */
	private $updatedAt;



	/*****************************************************/
	/* special functions                                 */
	/*****************************************************/
	/**
	 * Construct
	 */
	public function __construct()
	{
	}

	/**
	 * lifecycle prePersist
	 *
	 * @ORM\PrePersist
	 */
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = $this->createdAt;
	}

	/**
	 * lifecycle preUpdate
	 *
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}


	/*****************************************************/
	/* setter and getter                                 */
	/*****************************************************/
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
	 * Get task
	 *
	 * @return ArusTask
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Set task
	 *
	 * @param ArusTask $task
	 *
	 * @return ArusTaskCallback
	 */
	public function setTask( ArusTask $task)
	{
		$this->task = $task;
		if( is_object($task) ) {
			$task->addCallback($this);
		}

		return $this;
	}

	/**
	 * Set priority
	 *
	 * @param string $priority
	 *
	 * @return ArusEntityAlert
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get priority
	 *
	 * @return string
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
     * Set regex
     *
     * @param string $regex
     *
     * @return ArusTaskCallback
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get regex
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

	/**
     * Set action
     *
     * @param string $action
     *
     * @return ArusTaskCallback
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

	/**
     * Set params
     *
     * @param mixed $params
     *
     * @return ArusTaskCallback
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return mixed
     */
    public function getParams()
    {
		$params = $this->params;

		if( is_array($params) ) {
			ksort($params);
		}

        return (array)$params;
    }

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 *
	 * @return ArusTask
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt
	 *
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * Set updatedAt
	 *
	 * @param \DateTime $updatedAt
	 *
	 * @return ArusTask
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * Get updatedAt
	 *
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}


	/*****************************************************/
	/* manage params                                     */
	/*****************************************************/
	public function addParam( $key, $value ) {
		$this->params[$key] = $value;
		return $this;
	}
	public function getParam( $key ) {
		if( isset($this->params[$key]) ) {
			return $this->params[$key];
		} else {
			return null;
		}
	}
	public function removeParam( $key ) {
		if( isset($this->params[$key]) ) {
			unset( $this->params[$key] );
			return $this;
		} else {
			return null;
		}
	}

	private $param_text;
	public function setParamText( $text ) {
		$this->addParam( 'text', $text );
		return $this;
	}
	public function getParamText() {
		return $this->getParam('text');
	}

	private $param_task;
	public function setParamTask( $task ) {
		$this->addParam( 'task', $task );
		return $this;
	}
	public function getParamTask() {
		return $this->getParam('task');
	}

	private $param_alert_level;
	public function setParamAlertLevel( $alert_level ) {
		$this->addParam( 'alert_level', $alert_level );
		return $this;
	}
	public function getParamAlertLevel() {
		return $this->getParam('alert_level');
	}

	private $param_technology;
	public function setParamTechnology( $technology ) {
		$this->addParam( 'technology', $technology );
		return $this;
	}
	public function getParamTechnology() {
		return $this->getParam('technology');
	}
}

