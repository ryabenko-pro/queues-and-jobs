<?php

namespace Mobillogix\Launchpad\JobsBundle\Tests;


use Mobillogix\Launchpad\JobsBundle\Repository\JobEventRepository;
use Mobillogix\Launchpad\JobsBundle\Repository\JobPackageRepository;
use Mobillogix\Launchpad\JobsBundle\Repository\JobRepository;
use Mobillogix\Launchpad\JobsBundle\Repository\JobTypeRepository;
use Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobPersistenceService;
use Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobProcessPersistenceService;
use Mobillogix\Launchpad\JobsBundle\Service\ProcessExecutorService;
use Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess;
use PHPUnit_Framework_MockObject_MockObject;

class BaseJobsTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $calls ['...:...Repository' => $repository]
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManagerMock($calls = [])
    {
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])->getMock();

        $i = 0;
        foreach ($calls as $repo => $result) {
            if (is_numeric($repo)) {
                $i = $repo;
                $em->expects($this->at($i++))->method('getRepository')
                    ->with($result[0])->willReturn($result[1]);
            } else {
                $em->expects($this->at($i++))->method('getRepository')
                    ->with($repo)->willReturn($result);
            }
        }

        return $em;
    }

    /**
     * @param array $methods
     * @return JobPackageRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPackageRepositoryMock($methods = ['savePackage', 'getPackagesForRun'])
    {
        $jobPackageRepository = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Repository\JobPackageRepository')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();
        return $jobPackageRepository;
    }

    /**
     * @param array $methods
     * @return JobRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJobRepositoryMock($methods = ['saveJob', 'findAll'])
    {
        $jobTypeRepository = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Repository\JobRepository')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();

        return $jobTypeRepository;
    }

    /**
     * @param array $methods
     * @return JobTypeRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJobTypeRepositoryMock($methods = ['findAll'])
    {
        $jobTypeRepository = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Repository\JobTypeRepository')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();
        return $jobTypeRepository;
    }

    /**
     * @param array $methods
     * @return JobEventRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventRepositoryMock($methods = ['saveJobEvent'])
    {
        $jobEventRepository = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Repository\JobEventRepository')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();

        return $jobEventRepository;
    }

    /**
     * @param $methods
     * @return JobProcessPersistenceService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getProcessPersistenceMock($methods)
    {
        $processPersistence = $this->getMockBuilder('\Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobProcessPersistenceService')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();

        return $processPersistence;
    }

    /**
     * @param $methods
     * @return StubProcess|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getProcessMock($methods)
    {
        $process = $this->getMockBuilder('\Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess')
            ->disableOriginalConstructor()
            ->setMethods((array)$methods)->getMock();

        return $process;
    }

    /**
     * @param $methods
     * @return ProcessExecutorService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProcessExecutorMock($methods)
    {
        $executor = $this->getMockBuilder('\Mobillogix\Launchpad\JobsBundle\Service\ProcessExecutorService')
            ->disableOriginalConstructor()
            ->setMethods($methods)->getMock();

        return $executor;
    }

    /**
     * @param $methods
     * @return JobPersistenceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJobPersistenceServiceMock($methods = ['getJobsForPlanning', 'markJobPlanned'])
    {
        $jobPersist = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobPersistenceService')
            ->disableOriginalConstructor()
            ->setMethods($methods)->getMock();

        return $jobPersist;
    }

    /**
     * @param $methods
     * @return JobProcessPersistenceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProcessPersistenceServiceMock($methods = ['savePackage'])
    {
        $jobProcessPersist = $this->getMockBuilder('Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobProcessPersistenceService')
            ->setMethods($methods)
            ->disableOriginalConstructor()->getMock();

        return $jobProcessPersist;
    }

}