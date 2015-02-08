<?php

namespace Mobillogix\Launchpad\JobsBundle\Tests\Process;


use Mobillogix\Launchpad\JobsBundle\Entity\Job;
use Mobillogix\Launchpad\JobsBundle\Entity\JobEvent;
use Mobillogix\Launchpad\JobsBundle\Entity\JobPackage;
use Mobillogix\Launchpad\JobsBundle\Entity\JobType;
use Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobProcessPersistenceService;
use Mobillogix\Launchpad\JobsBundle\Tests\BaseJobsTestCase;
use Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess;

class JobProcessPersistenceServiceTest extends BaseJobsTestCase
{

    public function testShouldAddPackage()
    {
        // GIVEN
        $data = [["Some key" => "Some value"]];

        $types = [];
        $types[] = $this->createType("test");

        $jobEntity = new Job();
        $jobEntity
            ->setJobType($types[0])
            ->setData([]);
        $package = new JobPackage();
        $package
            ->setJob($jobEntity)
            ->setPackages($data);

        $packageRepository = $this->getPackageRepositoryMock('savePackage');
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobPackage' => $packageRepository,
        ]);

        $packageRepository->expects($this->once())
            ->method('savePackage')->with($package)
            ->willReturn(1);

        $service = new JobProcessPersistenceService($em, []);

        // WHEN
        $service->savePackage($package);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Job process class 'Mobillogix\Launchpad\JobsBundle\Tests\Process\JobProcessPersistenceServiceTest' must be subclass of Mobillogix\Launchpad\JobsBundle\Model\BaseProcess
     */
    public function testShouldThrowWrongParentClassException()
    {
        // GIVEN
        $types = [];
        $types[] = $this->createType("test");

        $entity = $this->getJobEntity($types[0], []);

        $packages = [];
        $packages[] = $this->createJobPackage($entity, [1, 2, 3, 4]);

        $packageRepository = $this->getPackageRepositoryMock('getPackagesForRun');
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobPackage' => $packageRepository,
        ]);

        $packageRepository->expects($this->once())->method('getPackagesForRun')
            ->willReturn($packages);

        $config = ['types' => []];
        $config['types']['test'] = ['process_class' => __CLASS__];
        $service = new JobProcessPersistenceService($em, $config);

        // WHEN
        $actual = $service->getProcessesForRun();
    }

    public function testShouldGetProcessesToRun()
    {
        // GIVEN
        $types = [];
        $types[] = $this->createType("test");

        $entity = $this->getJobEntity($types[0], []);

        $packages = [];
        $packages[] = $this->createJobPackage($entity, [1, 2, 3, 4]);
        $packages[] = $this->createJobPackage($entity, [5, 6, 7, 8]);

        $packageRepository = $this->getPackageRepositoryMock('getPackagesForRun');
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobPackage' => $packageRepository,
        ]);

        $packageRepository->expects($this->once())->method('getPackagesForRun')
            ->willReturn($packages);

        $config = ['types' => []];
        $config['types']['test'] = [
            'process_class' => 'Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess',
            'packages_chunk' => 10,
        ];
        $service = new JobProcessPersistenceService($em, $config);

        // WHEN
        $expected = [];
        $process = new StubProcess([1, 2, 3, 4]);
        $process->setEntity($packages[0]);
        $expected[] = $process;
        $process = new StubProcess([5, 6, 7, 8]);
        $process->setEntity($packages[1]);
        $expected[] = $process;

        $actual = $service->getProcessesForRun();
        $this->assertEquals($expected, $actual);
    }

    public function testShouldMarkProcessStarted()
    {
        // GIVEN
        $jobEntity = new Job();
        $entity = new JobPackage();
        $entity->setJob($jobEntity);

        $process = new StubProcess([1, 2, 3]);
        $process->setEntity($entity);

        $packageRepository = $this->getPackageRepositoryMock('savePackage');
        $eventRepository = $this->getEventRepositoryMock();
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobPackage' => $packageRepository,
            'MobillogixLaunchpadJobsBundle:JobEvent' => $eventRepository,
        ]);

        $service = new JobProcessPersistenceService($em, []);

        $expectedEntity = new JobPackage();
        $expectedEntity->setJob($jobEntity)
            ->setStatus(JobPackage::STATUS_RUN)
            ->setStartedAt(new \DateTime());

        $packageRepository->expects($this->once())->method('savePackage')
            ->with($this->equalTo($expectedEntity));

        $event = new JobEvent();
        $event->setJob($jobEntity)
            ->setJobPackage($expectedEntity)
            ->setType(JobEvent::TYPE_RUN);

        $eventRepository->expects($this->once())->method('saveJobEvent')
            ->with($this->equalTo($event));

        // WHEN

        $service->markPackageStarted($process);
    }

    public function testShouldMarkProcessFinished()
    {
        // GIVEN
        $jobEntity = new Job();
        $entity = new JobPackage();
        $entity->setJob($jobEntity);

        $process = new StubProcess([1, 2, 3]);
        $process->setEntity($entity);

        $packageRepository = $this->getPackageRepositoryMock('savePackage');
        $eventRepository = $this->getEventRepositoryMock();
        $jobRepository = $this->getJobRepositoryMock(['incPackagesFinished']);
        $em = $this->getEntityManagerMock([
            'MobillogixLaunchpadJobsBundle:JobPackage' => $packageRepository,
            'MobillogixLaunchpadJobsBundle:JobEvent' => $eventRepository,
            'MobillogixLaunchpadJobsBundle:Job' => $jobRepository,
        ]);

        $service = new JobProcessPersistenceService($em, []);

        $expectedEntity = new JobPackage();
        $expectedEntity->setJob($jobEntity)
            ->setStatus(JobPackage::STATUS_DONE)
            ->setFinishedAt(new \DateTime());

        $packageRepository->expects($this->once())->method('savePackage')
            ->with($this->equalTo($expectedEntity));

        $event = new JobEvent();
        $event->setJob($jobEntity)
            ->setJobPackage($expectedEntity)
            ->setType(JobEvent::TYPE_DONE);

        $eventRepository->expects($this->once())->method('saveJobEvent')
            ->with($this->equalTo($event));

        $jobRepository->expects($this->once())->method('incPackagesFinished')
            ->with($jobEntity);

        // WHEN

        $service->markPackageFinished($process);
    }

    /**
     * @return JobType
     */
    public function createType($slug)
    {
        $type = new JobType();
        $type->setSlug($slug)->setName($slug);

        return $type;
    }

    /**
     * @param JobType $type
     * @param mixed $data
     * @return Job
     */
    public function getJobEntity($type, $data)
    {
        $entity = new Job();
        $entity->setJobType($type)
            ->setData($data);
        return $entity;
    }

    /**
     * @param Job $job
     * @param array $packages
     * @return JobPackage
     */
    private function createJobPackage($job, $packages)
    {
        $package = new JobPackage();
        $package->setJob($job)
            ->setPackages($packages);

        return $package;
    }

}
