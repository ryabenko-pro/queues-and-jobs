<?php


namespace Mobillogix\QueueBundle\Tests\Service;


use Mobillogix\QueueBundle\Service\TaskSimpleExecutorService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Log\NullLogger;

class TaskSimpleExecutorServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldExecuteTest()
    {
        // GIVEN
        $container = new Container();
        $executor = new TaskSimpleExecutorService($container, new NullLogger());

        // EXPECTED
        $task = $this->getMockBuilder('Mobillogix\QueueBundle\Tests\StubTask')
            ->setConstructorArgs([[]])->setMethods(['beforeAdd', 'execute'])->getMock();

        $task->expects($this->once())->method('beforeAdd')->with($container);
        $task->expects($this->once())->method('execute')->with($container);

        $executor->addTask($task);
    }

}
