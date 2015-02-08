<?php

namespace Mobillogix\Launchpad\JobsBundle\Tests\Process;


use Mobillogix\Launchpad\JobsBundle\Tests\BaseJobsTestCase;
use Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess;
use Mobillogix\Launchpad\JobsBundle\Exception\MobillogixJobsException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProcessTest extends BaseJobsTestCase
{

    public function testShouldUpdateCurrentPackage()
    {
        // GIVEN
        $process = new StubProcess([1, 2]);
        $executor = $this->getProcessExecutorMock(['updatePackageNumber']);

        $executor->expects($this->at(0))->method('updatePackageNumber')
            ->with($this->equalTo($process), $this->equalTo(1));
        $executor->expects($this->at(1))->method('updatePackageNumber')
            ->with($this->equalTo($process), $this->equalTo(2));

        $container = new Container();
        $process->execute($executor, $container);
    }

    public function testShouldLogError()
    {
        // GIVEN
        /** @var StubProcess|\PHPUnit_Framework_MockObject_MockObject $process */
        $process = $this->getMockBuilder('\Mobillogix\Launchpad\JobsBundle\Tests\Stub\StubProcess')
            ->setConstructorArgs([[1, 2]])
            ->setMethods(['doExecute'])->getMock();
        $executor = $this->getProcessExecutorMock(['updatePackageNumber', 'addError']);

        $process->expects($this->at(0))->method('doExecute')
            ->with($this->equalTo(1));
        $process->expects($this->at(1))->method('doExecute')
            ->with($this->equalTo(2))->willThrowException(new MobillogixJobsException("Some message."));

        $executor->expects($this->any())->method('updatePackageNumber');
        $executor->expects($this->any())->method('addError')
            ->with($process, 2, "Error while executing task: 'Some message.'");

        $container = new Container();
        $process->execute($executor, $container);
    }

    public function testShouldLogOutput()
    {
        // GIVEN
        /** @var StubProcess|\PHPUnit_Framework_MockObject_MockObject $process */
        $process = new EchoProcess([null, 'Hello', null]);
        $executor = $this->getProcessExecutorMock(['updatePackageNumber', 'addLog']);

        $executor->expects($this->once())->method('addLog')
            ->with($process, "Hello", 0);

        $container = new Container();
        $process->execute($executor, $container);
    }

}

class EchoProcess extends StubProcess
{

    protected function doExecute($package, ContainerInterface $container)
    {
        if (!is_null($package)) {
            echo $package;
        }
    }

}