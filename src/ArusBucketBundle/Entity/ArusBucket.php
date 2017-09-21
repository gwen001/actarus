<?php

namespace ArusBucketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use ArusProjectBundle\Entity\ArusProject;
use ArusHostBundle\Entity\ArusHost;

use Actarus\Utils;


/**
 * ArusBucket
 *
 * @ORM\Entity(repositoryClass="ArusBucketBundle\Repository\ArusBucketRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArusBucket
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
     * @ORM\ManyToOne(targetEntity="ArusProjectBundle\Entity\ArusProject", inversedBy="buckets")
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="perm_set_acl", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $permSetACL;

    /**
     * @var int
     *
     * @ORM\Column(name="perm_get_acl", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $permGetACL;

    /**
     * @var int
     *
     * @ORM\Column(name="perm_list_api", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $permReadAPI;

    /**
     * @var int
     *
     * @ORM\Column(name="perm_list_http", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $permReadHTTP;

    /**
     * @var int
     *
     * @ORM\Column(name="perm_write", type="smallint", options={"unsigned"=true,"default":0})
     */
    private $permWrite;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", options={"unsigned"=true,"default":0})
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
	 * Set project
	 *
	 * @param ArusProject $project
	 *
	 * @return ArusBucket
	 */
	public function setProject(ArusProject $project)
	{
		$this->project = $project;
		$project->addBucket( $this );

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
     * Set name
     *
     * @param string $name
     *
     * @return ArusBucket
     */
    public function setName($name)
    {
        $this->name = strtolower($name);

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
     * Set setPermSetACL
     *
     * @param integer $v
     *
     * @return ArusBucket
     */
    public function setPermSetACL($v)
    {
        $this->permSetACL = (int)$v;

        return $this;
    }

    /**
     * Get permSetACL
     *
     * @return string
     */
    public function getPermSetACL()
    {
        return $this->permSetACL;
    }

    /**
     * Set setPermGetACL
     *
     * @param integer $v
     *
     * @return ArusBucket
     */
    public function setPermGetACL($v)
    {
        $this->permGetACL = (int)$v;

        return $this;
    }

    /**
     * Get permGetACL
     *
     * @return string
     */
    public function getPermGetACL()
    {
        return $this->permGetACL;
    }

    /**
     * Set setPermReadAPI
     *
     * @param integer $v
     *
     * @return ArusBucket
     */
    public function setPermReadAPI($v)
    {
        $this->permReadAPI = (int)$v;

        return $this;
    }

    /**
     * Get permReadAPI
     *
     * @return string
     */
    public function getPermReadAPI()
    {
        return $this->permReadAPI;
    }

    /**
     * Set setPermReadHTTP
     *
     * @param integer $v
     *
     * @return ArusBucket
     */
    public function setPermReadHTTP($v)
    {
        $this->permReadHTTP = (int)$v;

        return $this;
    }

    /**
     * Get permReadHTTP
     *
     * @return string
     */
    public function getPermReadHTTP()
    {
        return $this->permReadHTTP;
    }

    /**
     * Set setPermWrite
     *
     * @param integer $v
     *
     * @return ArusBucket
     */
    public function setPermWrite($v)
    {
        $this->permWrite = (int)$v;

        return $this;
    }

    /**
     * Get permWrite
     *
     * @return string
     */
    public function getPermWrite()
    {
        return $this->permWrite;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return ArusBucket
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
     * @return ArusBucket
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
     * @return ArusBucket
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
}
