<?php

namespace Mobillogix\JobsBundle\Tests\Process;


use Mobillogix\JobsBundle\Entity\Job;
use Mobillogix\JobsBundle\Entity\JobEvent;
use Mobillogix\JobsBundle\Entity\JobPackage;
use Mobillogix\JobsBundle\Service\ProcessExecutorService;
use Mobillogix\JobsBundle\Tests\BaseJobsTestCase;
use Symfony\Component\DependencyInjection\Container;

class ProcessExecutorServiceTest extends BaseJobsTestCase
{

    public function testShouldRunProcess()
    {
        // GIVEN
        $container = new Container();

        $processPersistence = $this->getProcessPersistenceMock(['markPackageStarted', 'markPackageFinished']);
        $process = $this->getProcessMock('execute');

        $service = new ProcessExecutorService($processPersistence, $container);

        $process->expects($this->once())->method('execute')
            ->with($this->equalTo($service), $this->equalTo($container));

        $processPersistence->expects($this->once())->method('markPackageStarted')
            ->with($this->equalTo($process));

        $processPersistence->expects($this->once())->method('markPackageFinished')
            ->with($this->equalTo($process));

        // WHEN
        $service->runProcess($process);
    }

    public function testShouldLogException()
    {
        // GIVEN
        $container = new Container();
        $job = new Job();
        $entity = new JobPackage();
        $entity->setJob($job);

        $processPersistence = $this->getProcessPersistenceMock(['markPackageStarted', 'addEvent']);
        $process = $this->getProcessMock('execute');
        $process->setEntity($entity);

        $service = new ProcessExecutorService($processPersistence, $container);

        $event = new JobEvent();
        $event->setJob($job)
            ->setJobPackage($entity)
            ->setType(JobEvent::TYPE_ERROR)
            ->setJobPackageNumber(2)
            ->setMessage("Exception '\\Exception' while executing task: 'Some exception.'");

        $processPersistence->expects($this->once())->method('addEvent')
            ->with($this->equalTo($event));

        // WHEN
        $service->addError($process, 2, "Exception '\\Exception' while executing task: 'Some exception.'");
    }

}