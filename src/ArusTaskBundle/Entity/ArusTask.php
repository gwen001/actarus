<?php

namespace ArusTaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ArusProjectBundle\Entity\ArusProject;
use ArusTaskCallbackBundle\Entity\ArusTaskCallback;


/**
 * ArusTask
 *
 * @ORM\Table(name="arus_task")
 * @ORM\Entity(repositoryClass="ArusTaskBundle\Repository\ArusTaskRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusTask
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="text", unique=false)
     */
    private $command;

    /**
     * @var string
     *
     * @ORM\Column(name="entities", type="object", nullable=true)
     */
    private $entities;

    /**
     * @var string
     *
     * @ORM\Column(name="default_options", type="object")
     */
    private $defaultOptions;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", options={"unsigned"=true})
     */
    private $timeout;

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

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="ArusTaskCallbackBundle\Entity\ArusTaskCallback", mappedBy="task")
	 */
	private $callbacks;


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
     * Set name
     *
     * @param string $name
     *
     * @return ArusTask
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set command
     *
     * @param string $command
     *
     * @return ArusTask
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

	/**
     * Set entities
     *
     * @param mixed $entities
     *
     * @return ArusTask
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Get entities
     *
     * @return mixed
     */
    public function getEntities( $entity=null )
    {
		$entities = $this->entities;

		if( $entity ) {
			if( isset($entities[$entity]) ) {
				return $entities[ $entity ];
			} else {
				return false;
			}
		}
		else {
        	return (array)$entities;
		}
    }

	/**
     * Set defaultOptions
     *
     * @param mixed $defaultOptions
     *
     * @return ArusTask
     */
    public function setDefaultOptions($defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;

        return $this;
    }

    /**
     * Get defaultOptions
     *
     * @return mixed
     */
    public function getDefaultOptions()
    {
		$default_options = $this->defaultOptions;

		if( is_array($default_options) ) {
			ksort($default_options);
		}

        return (array)$default_options;
    }

    /**
     * Set timeout
     *
     * @param string $timeout
     *
     * @return ArusTask
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get timeout
     *
     * @return string
     */
    public function getTimeout()
    {
        return $this->timeout;
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
	/* related objects                                   */
	/*****************************************************/
	public function addCallback(ArusTaskCallback $callback) {
		$this->callbacks[] = $callback;
		return $this;
	}
	public function removeCallback(ArusTaskCallback $callback) {
		$this->callbacks->removeElement( $callback );
	}
	public function getCallbacks() {
		return $this->callbacks;
	}


	/*****************************************************/
	/* custom functions                                  */
	/*****************************************************/
	private $binaryExists = null;

    public function getBinaryExists() {
        return $this->binaryExists;
    }
	public function setBinaryExists( $binaryExists ) {
        $this->binaryExists = $binaryExists;
        return $this;
    }
}
