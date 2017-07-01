<?php

namespace ArusEntityTaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

use ArusProjectBundle\Entity\ArusProject;
use ArusTaskBundle\Entity\ArusTask;

use Actarus\Utils;


/**
 * ArusEntityTask
 *
 * @ORM\Table(name="arus_entity_task", indexes={@Index(name="idx_entity_id", columns={"entity_id"}),@Index(name="idx_status", columns={"status"})})
 * @ORM\Entity(repositoryClass="ArusEntityTaskBundle\Repository\ArusEntityTaskRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusEntityTask
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
	 * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="tasks")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $project;

	/**
	 * @ORM\ManyToOne(targetEntity="ArusTaskBundle\Entity\ArusTask")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $task;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="entity_id", type="string", length=64, nullable=false, unique=false)
	 */
	private $entityId;

	/**
     * @var string
     *
     * @ORM\Column(name="command", type="text", nullable=false)
     */
    private $command;

    /**
     * @var string
     *
     * @ORM\Column(name="output", type="text", nullable=true)
     */
    private $output;

    /**
     * @var int
     *
     * @ORM\Column(name="pid", type="smallint", nullable=true, options={"unsigned"=true})
     */
    private $pid;

    /**
     * @var int
     *
     * @ORM\Column(name="real_pid", type="smallint", nullable=true, options={"unsigned"=true})
     */
    private $realPid;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="cluster_id", type="smallint", nullable=true, options={"unsigned"=true})
	 */
	private $clusterId;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="priority", type="smallint", nullable=true, options={"unsigned"=true})
	 */
	private $priority;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false, options={"unsigned"=true,"default":0})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ended_at", type="datetime", nullable=true)
     */
    private $endedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="kill_at", type="datetime", nullable=true)
     */
    private $killAt;


	/*****************************************************/
	/* special functions                                 */
	/*****************************************************/
	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->status = 0;
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
     * Set entityId
     *
     * @param string $entityId
     *
     * @return ArusEntityTask
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
     * Set command
     *
     * @param string $command
     *
     * @return ArusEntityTask
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
     * Set output
     *
     * @param string $output
     *
     * @return ArusEntityTask
     */
    public function setOutput($output)
    {
        $this->output = Utils::cleanOutput( trim($output) );

        return $this;
    }

    /**
     * Get output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set pid
     *
     * @param string $pid
     *
     * @return ArusEntityTask
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set realPid
     *
     * @param string $realPid
     *
     * @return ArusEntityTask
     */
    public function setRealpid($realPid)
    {
        $this->realPid = $realPid;

        return $this;
    }

    /**
     * Get realPid
     *
     * @return string
     */
    public function getRealPid()
    {
        return $this->realPid;
    }

    /**
     * Set clusterId
     *
     * @param string $clusterId
     *
     * @return ArusEntityTask
     */
    public function setClusterId($clusterId)
    {
        $this->clusterId = $clusterId;

        return $this;
    }

    /**
     * Get clusterId
     *
     * @return string
     */
    public function getClusterId()
    {
        return $this->clusterId;
    }

    /**
     * Set priority
     *
     * @param string $proirity
     *
     * @return ArusEntityTask
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
     * Set status
     *
     * @param string $status
     *
     * @return ArusEntityTask
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ArusEntityTask
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
     * @return ArusEntityTask
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

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     *
     * @return ArusEntityTask
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set endedAt
     *
     * @param \DateTime $endedAt
     *
     * @return ArusEntityTask
     */
    public function setEndedAt($endedAt)
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * Get endedAt
     *
     * @return \DateTime
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * Set killAt
     *
     * @param \DateTime $killAt
     *
     * @return ArusEntityTask
     */
    public function setKillAt($killAt)
    {
        $this->killAt = $killAt;

        return $this;
    }

    /**
     * Get killAt
     *
     * @return \DateTime
     */
    public function getKillAt()
    {
        return $this->killAt;
    }


	/*****************************************************/
	/* related objects                                   */
	/*****************************************************/
	/**
	 * Set project
	 *
	 * @param ArusProject $project
	 *
	 * @return ArusEntityTask
	 */
	public function setProject(ArusProject $project) {
		$this->project = $project;
		return $this;
	}
	/**
	 * Get project
	 *
	 * @return ArusProject
	 */
	public function getProject() {
		return $this->project;
	}


	public function getEntity() {
		return $this->entity;
	}
	public function setEntity( $entity ) {
		$this->entity = $entity;
		return $this;
	}

	public function getTask() {
		return $this->task;
	}
	public function setTask( ArusTask $task ) {
		$this->task = $task;
		return $this;
	}


	/*****************************************************/
	/* custom functions                                  */
	/*****************************************************/
	public function getDuration() {
		if( !$this->startedAt ) {
			return false;
		}

		if( $this->endedAt ) {
			$end = $this->endedAt;
		} else {
			$end = new \DateTime();
		}

		$duration = '';
		$diff = $this->startedAt->diff( $end );
		if( $diff->d ) {
			$duration .= $diff->d.'d';
		}
		if( $diff->h ) {
			$duration .= $diff->h.'h';
		}
		if( $diff->i ) {
			$duration .= $diff->i.'m';
		}
		if( $diff->s ) {
			$duration .= $diff->s.'s';
		}

		return $duration;
	}
}
