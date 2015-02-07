<?php

namespace Mobillogix\JobsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mobillogix\JobsBundle\Util\TimestampableEntity;


/**
 * @ORM\Table(name="job_type")
 * @ORM\Entity(repositoryClass="Mobillogix\JobsBundle\Repository\JobTypeRepository")
 */
class JobType
{

    use TimestampableEntity;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @ORM\Column(type="float")
     */
    protected $priority;

    /**
     * @ORM\Column(name="planning_interval", type="integer", nullable=true)
     */
    protected $planningInterval;

    /**
     * Get id
     *
     * @return integer
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
     * @return self
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
     * Set slug
     *
     * @param string $slug
     *
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set priority
     *
     * @param float $priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return mixed
     */
    public function getPlanningInterval()
    {
        return $this->planningInterval;
    }

    /**
     * @param mixed $planningInterval
     * @return self
     */
    public function setPlanningInterval($planningInterval)
    {
        $this->planningInterval = $planningInterval;
        return $this;
    }

}
