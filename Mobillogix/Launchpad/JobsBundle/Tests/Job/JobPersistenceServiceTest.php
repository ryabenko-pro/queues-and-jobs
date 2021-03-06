<?php

namespace Mobillogix\Launchpad\JobsBundle\Tests\Job;


use Mobillogix\Launchpad\JobsBundle\Entity\Job;
use Mobillogix\Launchpad\JobsBundle\Entity\JobEvent;
use Mobillogix\Launchpad\JobsBundle\Entity\JobType;
use Mobillogix\Launchpad\JobsBundle\Model\BaseJob;
use Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobPersistenceService;
use Mobillogix\Launchpad\JobsBundle\Tests\BaseJobsTestCase;
use Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubJob;
use PHPUnit_Framework_MockObject_MockObject;

class JobPersistenceServiceTest extends BaseJobsTestCase
{

    protected $config = [
        'types' => [
            'test' => [
                'name'  => 'Test',
                'priority'  => 0,
                'job_class' => 'Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubJob',
                'process_class' => 'Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubJob',
                'packages_chunk' => 10,
                'planning_interval' => 11,
            ]
        ]
    ];

    public function testShouldAddJob()
    {
        // GIVEN
        $data = ["Some key" => "Some value"];

        $types = [];
        $type = new JobType();
        $type->setSlug("not_used")->setName("Not used");
        $types[] = $type;
        $type = new JobType();
        $type->setSlug("test")->setName("Test");
        $types[] = $type;

        $expected = new Job();
        $expected->setData($data)
            ->setJobType($type);

        $jobTypeRepository = $this->getJobTypeRepositoryMock(['findAll']);
        $jobRepository = $this->getJobRepositoryMock(['saveJob']);
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobType' => $jobTypeRepository,
            'MobillogixLaunchpadJobsBundle:Job' => $jobRepository,
        ]);

        $jobTypeRepository->expects($this->once())->method('findAll')
            ->willReturn($types);

        $jobRepository->expects($this->once())
            ->method('saveJob')->with($expected)
            ->willReturn(1);

        $job = $this->getJobMock(null, $data, 'test', ['getType', 'setEntity']);
        $job->expects($this->once())->method('setEntity')
            ->with($this->equalTo($expected));

        $service = new JobPersistenceService($em, []);

        // WHEN
        $service->addJob($job);
    }

    public function testShouldCreateJobType()
    {
        // GIVEN
        $types = [];

        $type = new JobType();
        $type->setName('Test')
            ->setPlanningInterval(11)
            ->setPriority(0)
            ->setSlug('test');

        $jobTypeRepository = $this->getJobTypeRepositoryMock(['findAll', 'saveJobType']);
        $jobRepository = $this->getJobRepositoryMock(['saveJob']);
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobType' => $jobTypeRepository,
            'MobillogixLaunchpadJobsBundle:Job' => $jobRepository,
        ]);

        $jobTypeRepository->expects($this->once())->method('findAll')
            ->willReturn($types);
        $jobTypeRepository->expects($this->once())->method('saveJobType')
            ->with($this->equalTo($type));

        $jobRepository->expects($this->once())
            ->method('saveJob')->with($this->anything())
            ->willReturn(1);

        $job = $this->getJobMock(null, [[]]);

        $job->expects($this->once())->method('getType')
            ->willReturn('test');

        $service = new JobPersistenceService($em, $this->config);

        // WHEN
        $service->addJob($job);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Config for job type 'test' not found in parameters.
     */
    public function testShouldThrowConfigNotFoundException()
    {
        // GIVEN
        $data = ['Some key' => 'some value'];

        $types = [];
        $type = new JobType();
        $type->setSlug("test")->setName("Test")->setPlanningInterval(11);
        $types[] = $type;

        $entities = [];
        $entity = new Job();
        $entity
            ->setData($data)
            ->setIsNeedPlanning(true)
            ->setJobType($type)
            ->setStatus(Job::STATUS_NEW);
        $entities[] = $entity;

        $jobTypeRepository = $this->getJobTypeRepositoryMock(['findAll']);
        $jobRepository = $this->getJobRepositoryMock(['findJobsForPlanning']);
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobType' => $jobTypeRepository,
            'MobillogixLaunchpadJobsBundle:Job' => $jobRepository,
        ]);

        $jobRepository->expects($this->once())->method('findJobsForPlanning')
            ->willReturn($entities);

        $service = new JobPersistenceService($em, []);

        // WHEN
        $jobs = $service->getJobsForPlanning();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Job class 'Mobillogix\Launchpad\JobsBundle\Tests\Job\JobPersistenceServiceTest' must be subclass of Mobillogix\Launchpad\JobsBundle\Model\BaseJob
     */
    public function testShouldThrowWrongParentClassException()
    {
        // GIVEN
        $data = ['Some key' => 'some value'];

        $types = [];
        $type = new JobType();
        $type->setSlug("test")->setName("Test")->setPlanningInterval(11);
        $types[] = $type;

        $entities = [];
        $entity = new Job();
        $entity
            ->setData($data)
            ->setIsNeedPlanning(true)
            ->setJobType($type)
            ->setStatus(Job::STATUS_NEW);
        $entities[] = $entity;

        $jobRepository = $this->getJobRepositoryMock(['findJobsForPlanning']);
        $em = $this->getEntityManagerMock([
            1 => ['MobillogixLaunchpadJobsBundle:Job', $jobRepository],
        ]);

        $jobRepository->expects($this->once())->method('findJobsForPlanning')
            ->willReturn($entities);

        $config = ['types' => []];
        $config['types']['test'] = ['job_class' => __CLASS__];
        $service = new JobPersistenceService($em, $config);

        // WHEN
        $jobs = $service->getJobsForPlanning();
    }

    public function testShouldReturnJobsForPlanning()
    {
        // GIVEN
        $data = ['Some key' => 'some value'];


        $types = [];
        $type = new JobType();
        $type->setSlug("test")->setName("Test");
        $types[] = $type;

        $entities = [];
        $entity = new Job();
        $entity
            ->setData($data)
            ->setIsNeedPlanning(true)
            ->setJobType($type)
            ->setStatus(Job::STATUS_NEW);
        $entities[] = $entity;

        $jobRepository = $this->getJobRepositoryMock(['findJobsForPlanning']);
        $em = $this->getEntityManagerMock([
            1 => ['MobillogixLaunchpadJobsBundle:Job', $jobRepository],
        ]);

        $jobRepository->expects($this->once())->method('findJobsForPlanning')
            ->willReturn($entities);

        $service = new JobPersistenceService($em, $this->config);

        // WHEN
        $jobs = $service->getJobsForPlanning();

        $expected = [];
        $job = new StubJob($data);
        $job->setEntity($entity);
        $expected[] = $job;
        $this->assertEquals($expected, $jobs);
    }

    public function testShouldMarkJobPlanned()
    {
        // GIVEN
        $data = ['Some key' => 'some value'];
        $modifiedData = ['some', 'modified', 'data'];

        $types = [];
        $type = new JobType();
        $type->setSlug("test")->setName("Test");
        $types[] = $type;

        $entity = new Job();
        $entity
            ->setPlannedAt(new \DateTime())
            ->setData($data)
            ->setIsNeedPlanning(true)
            ->setJobType($type)
            ->setStatus(Job::STATUS_NEW);

        $jobEventRepository = $this->getEventRepositoryMock(['saveJobEvent']);
        $jobRepository = $this->getJobRepositoryMock(['saveJob']);
        $em = $this->getEntityManagerMock([
            1 => ['MobillogixLaunchpadJobsBundle:Job', $jobRepository],
        ]);

        $em->expects($this->at(2))->method('getRepository')
            ->with($this->equalTo('MobillogixLaunchpadJobsBundle:JobEvent'))
            ->willReturn($jobEventRepository);

        $service = new JobPersistenceService($em, $this->config);

        $job = $this->getJobMock($entity, $data, 'test', ['getType', 'getData', 'isNeedsPlanning']);
        $job->expects($this->once())->method('getData')->willReturn($modifiedData);
        $job->expects($this->once())->method('isNeedsPlanning')->willReturn(false);

        $event = new JobEvent();
        $event->setType(JobEvent::TYPE_PLAN)
            ->setJob($entity);

        $expectedEntity = new Job();
        $expectedEntity
            ->setNextPlanningAt(new \DateTime("+11 seconds"))
            ->setIsNeedPlanning(false)
            ->setStatus($expectedEntity::STATUS_RUN)
            ->setJobType($type)
            ->setData($modifiedData)
            ->setPlannedAt(new \DateTime());

        $jobRepository->expects($this->once())->method('saveJob')
            ->with($this->equalTo($expectedEntity));

        $jobEventRepository->expects($this->once())->method('saveJobEvent')
            ->with($event);

        // WHEN
        $service->markJobPlanned($job);
    }

    public function testShouldMarkJobDone()
    {
        // GIVEN
        $data = ['Some key' => 'some value'];
        $modifiedData = ['some', 'modified', 'data'];

        $types = [];
        $type = new JobType();
        $type->setSlug("test")->setName("Test");
        $types[] = $type;

        $entity = new Job();
        $entity
            ->setData($data)
            ->setIsNeedPlanning(true)
            ->setJobType($type)
            ->setStatus(Job::STATUS_NEW);

        $jobEventRepository = $this->getEventRepositoryMock(['saveJobEvent']);
        $jobRepository = $this->getJobRepositoryMock(['saveJob']);
        $em = $this->getEntityManagerMock([
            1 => ['MobillogixLaunchpadJobsBundle:Job', $jobRepository],
        ]);

        $em->expects($this->at(2))->method('getRepository')
            ->with($this->equalTo('MobillogixLaunchpadJobsBundle:JobEvent'))
            ->willReturn($jobEventRepository);

        $service = new JobPersistenceService($em, $this->config);

        $job = $this->getJobMock($entity, $data, 'test', ['getType', 'getData', 'isNeedsPlanning']);
        $job->expects($this->once())->method('isNeedsPlanning')->willReturn(false);

        $job->expects($this->once())->method('getData')->willReturn($modifiedData);

        $event = new JobEvent();
        $event->setType(JobEvent::TYPE_DONE)
            ->setJob($entity);

        $expectedEntity = new Job();
        $expectedEntity
            ->setFinishedAt(new \DateTime())
            ->setNextPlanningAt(new \DateTime("+11 seconds"))
            ->setIsNeedPlanning(false)
            ->setStatus($expectedEntity::STATUS_DONE)
            ->setJobType($type)
            ->setData($modifiedData);

        $jobRepository->expects($this->once())->method('saveJob')
            ->with($this->equalTo($expectedEntity));

        $jobEventRepository->expects($this->once())->method('saveJobEvent')
            ->with($event);

        // WHEN
        $service->markJobDone($job);
    }

    public function testShouldSaveJob()
    {
        // GIVEN
        $entity = new Job();
        $entity->setData([]);

        $jobRepository = $this->getJobRepositoryMock(['saveJob']);
        $em = $this->getEntityManagerMock([
            1 => ['MobillogixLaunchpadJobsBundle:Job', $jobRepository],
        ]);

        $service = new JobPersistenceService($em, $this->config);

        /** @var BaseJob|PHPUnit_Framework_MockObject_MockObject $job */
        $job = $this->getJobMock($entity, [], 'test', ['getType', 'getNextPlanningAt', 'isNeedsPlanning']);
        $job->expects($this->once())->method('getNextPlanningAt')->willReturn(null);
        $job->expects($this->once())->method('isNeedsPlanning')->willReturn(false);

        $expectedEntity = clone $entity;
        $expectedEntity
            ->setIsNeedPlanning(false)
            ->setNextPlanningAt(new \DateTime("+11 seconds"));

        $jobRepository->expects($this->once())->method('saveJob')
            ->with($this->equalTo($expectedEntity));

        // WHEN
        $service->saveJob($job);
    }

    /**
     * @param $entity
     * @param array $data
     * @param string $type
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJobMock($entity = null, $data = [], $type = 'test', $methods = ['getType', 'isNeedsPlanning'])
    {
        /** @var StubJob|PHPUnit_Framework_MockObject_MockObject $job */
        $job = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubJob')
            ->setConstructorArgs([$data])->setMethods($methods)->getMock();

        if (!is_null($entity)) {
            $job->setEntity($entity);
        }

        if (!is_null($type)) {
            $job->expects($this->any())->method('getType')
                ->willReturn($type);
        }

        return $job;
    }

}
