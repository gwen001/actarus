<?php

namespace ArusTechnologyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArusTechnology
 *
 * @ORM\Table(name="arus_technology")
 * @ORM\Entity(repositoryClass="ArusTechnologyBundle\Repository\ArusTechnologyRepository")
 */
class ArusTechnology
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="implies", type="object", nullable=true)
     */
    private $implies;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255, unique=false)
     */
    private $icon;


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
     * @return ArusTechnology
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
     * Set implies
     *
     * @param mixed $implies
     *
     * @return ArusTaskCallback
     */
    public function setImplies($implies)
    {
        $this->implies = $implies;

        return $this;
    }

    /**
     * Get implies
     *
     * @return mixed
     */
    public function getImplies()
    {
		$implies = $this->implies;

		if( is_array($implies) ) {
			ksort($implies);
		}

        return (array)$implies;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return ArusTechnology
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}

