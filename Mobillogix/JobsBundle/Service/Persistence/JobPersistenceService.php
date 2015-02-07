<?php

namespace Mobillogix\JobsBundle\Service\Persistence;


use Mobillogix\JobsBundle\Entity\Job;
use Mobillogix\JobsBundle\Entity\JobEvent;
use Mobillogix\JobsBundle\Entity\JobType;
use Mobillogix\JobsBundle\Model\BaseJob;
use Mobillogix\JobsBundle\Repository\JobEventRepository;
use Mobillogix\JobsBundle\Repository\JobRepository;
use Mobillogix\JobsBundle\Repository\JobTypeRepository;
use Doctrine\ORM\EntityManager;

class JobPersistenceService
{

    const PLANNING_LIMIT = 10;
    const PARENT_JOB_CLASS = 'Mobillogix\JobsBundle\Model\BaseJob';

    protected $config;

    /** @var EntityManager */
    protected $em;
    /** @var JobTypeRepository */
    protected $jobTypeRepository;
    /** @var JobRepository */
    protected $jobRepository;
    /** @var JobEventRepository */
    protected $jobEventRepository;

    protected $types;

    /**
     * @param EntityManager $em
     * @param array $config Config content job definitions, grouped by types:
     * array('types' => array(
     *  'test' => array(
     *    'job_class' => 'Full\Class\Name',
     *    'process_class' => 'Full\Class\Name',
     *    'packages_chunk' => 10,
     *  )
     * ))
     */
    function __construct(EntityManager $em, $config)
    {
        $this->em = $em;
        $this->config = $config;

        $this->jobTypeRepository = $em->getRepository('CommonJobsBundle:JobType');
        $this->jobRepository = $em->getRepository('CommonJobsBundle:Job');
        $this->jobEventRepository = $em->getRepository('CommonJobsBundle:JobEvent');
    }

    /**
     * @param BaseJob $job
     * @return int
     * @throws \Exception
     */
    public function addJob(BaseJob $job)
    {
        $type = $this->getType($job->getType());

        $entity = new Job();
        $entity
            ->setJobType($type)
            ->setData($job->getData());

        $this->jobRepository->saveJob($entity);
        $job->setEntity($entity);

        return $entity->getId();
    }

    /**
     * @return BaseJob[]
     * @throws \Exception
     */
    public function getJobsForPlanning()
    {
        // TODO [far]: select and mark planned one job to run it multiprocessing
        // TODO [far]: Use proper transaction isolation level
        $entities = $this->jobRepository->findJobsForPlanning(self::PLANNING_LIMIT);

        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->mapEntity($entity);
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getJobsForFinishing()
    {
        $entities = $this->jobRepository->findJobsForFinishing(self::PLANNING_LIMIT);

        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->mapEntity($entity);
        }

        return $result;
    }

    /**
     * @param BaseJob $job
     */
    public function markJobPlanned(BaseJob $job)
    {
        $entity = $job->getEntity();
        $entity
            ->setPlannedAt(new \DateTime())
            ->setStatus(Job::STATUS_RUN);

        $this->saveJob($job);

        $event = new JobEvent();
        $event->setJob($entity)
            ->setType($event::TYPE_PLAN);

        $this->addEvent($event);
    }

    /**
     * @param BaseJob $job
     */
    public function markJobDone(BaseJob $job)
    {
        $entity = $job->getEntity();
        $entity
            ->setFinishedAt(new \DateTime())
            ->setStatus(Job::STATUS_DONE);

        $this->saveJob($job);

        $event = new JobEvent();
        $event->setJob($entity)
            ->setType($event::TYPE_DONE);

        $this->addEvent($event);
    }

    /**
     * @param $slug
     * @throws \Exception
     * @return JobType
     */
    protected function getType($slug)
    {
        if (is_null($this->types)) {
            $this->types = [];

            /* @var $type JobType */
            foreach ($this->jobTypeRepository->findAll() as $type) {
                $this->types[$type->getSlug()] = $type;
            }
        }

        if (!isset($this->types[$slug])) {
            $type = new JobType();
            $config = $this->config['types'][$slug];
            $type->setSlug($slug)
                ->setName($config['name'])
                ->setPlanningInterval($config['planning_interval'])
                ->setPriority($config['priority']);

            $this->jobTypeRepository->saveJobType($type);

            $this->types[$slug] = $type;
        }

        return $this->types[$slug];
    }

    /**
     * @param Job $entity
     * @return array
     * @throws \Exception
     */
    public function mapEntity($entity)
    {
        $typeSlug = $entity->getJobType()->getSlug();
        $data = $entity->getData();

        $job = $this->createJobInstance($typeSlug, $data);
        $job->setEntity($entity);

        return $job;
    }

    /**
     * @param JobEvent $event
     */
    public function addEvent(JobEvent $event)
    {
        $this->jobEventRepository->saveJobEvent($event);
    }

    /**
     * @param BaseJob $job
     * @return Job
     */
    public function saveJob(BaseJob $job)
    {
        $nextPlanningAt = $job->getNextPlanningAt();
        $interval = $this->config['types'][$job->getType()]['planning_interval'];

        // TODO: find way to select considering interval from type
        if (is_null($nextPlanningAt) && !is_null($interval)) {
            $nextPlanningAt = new \DateTime("+{$interval} seconds");
        }

        $entity = $job->getEntity();
        $entity->setData($job->getData())
            ->setNextPlanningAt($nextPlanningAt)
            ->setIsNeedPlanning($job->isNeedsPlanning());

        $this->jobRepository->saveJob($entity);
    }

    /**
     * @param string $type
     * @param mixed $data
     * @return BaseJob
     * @throws \Exception
     */
    public function createJobInstance($type, $data)
    {
        if (!isset($this->config['types'][$type])) {
            throw new \Exception("Config for job type '{$type}' not found in parameters.");
        }

        $config = $this->config['types'][$type];
        $class = $config['job_class'];
        if (!is_subclass_of($class, self::PARENT_JOB_CLASS)) {
            throw new \Exception("Job class '{$class}' must be subclass of " . self::PARENT_JOB_CLASS);
        }
        $job = new $class($data);
        return $job;
    }

}