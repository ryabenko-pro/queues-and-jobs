<?php


namespace Mobillogix\QueueBundle\Tests\Service;


use Mobillogix\QueueBundle\DependencyInjection\Util\ConfigQueuedTaskType;
use Mobillogix\QueueBundle\Entity\QueuedTask;
use Mobillogix\QueueBundle\Entity\QueuedTaskRepository;
use Mobillogix\QueueBundle\Exception\TaskExecutionException;
use Mobillogix\QueueBundle\Service\TaskLoggerInterface;
use Mobillogix\QueueBundle\Service\TaskQueueService;
use Mobillogix\QueueBundle\Tests\StubQueuedTask;
use Mobillogix\QueueBundle\Tests\StubTask;
use Symfony\Component\DependencyInjection\Container;

class TaskQueueServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Mobillogix\QueueBundle\Exception\TaskAddException
     * @expectedExceptionMessage Type 'stub' not found
     */
    public function testShouldThrowTypeNotFoundException()
    {
        // GIVEN
        $task = new StubTask(['some data']);

        $container = new Container();
        /** @var QueuedTaskRepository|\PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->getMockBuilder('Mobillogix\QueueBundle\Entity\QueuedTaskRepository')
            ->setMethods(['saveQueueTask', 'getQueuedTask', 'getQueuedTasksForRun'])
            ->disableOriginalConstructor()->getMock();

        $service = new TaskQueueService($container, $repository, []);

        // WHEN
        $service->addTask($task);
    }

    public function testShouldQueueTask()
    {
        // GIVEN
        /** @var StubTask|\PHPUnit_Framework_MockObject_MockObject $task */
        $task = $this->getMockBuilder('Mobillogix\QueueBundle\Tests\StubTask')
            ->setConstructorArgs([['some data']])->setMethods(['beforeAdd', 'execute'])->getMock();
        $type = StubTask::getConfigType();

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $entity = new QueuedTask();
        $entity->setType($task->getType())
            ->setData($task->getData())
            ->setPriority($type->getPriority());

        $task->expects($this->once())->method('beforeAdd')
            ->with($container);

        $repository->expects($this->once())
            ->method('saveQueuedTask')->with($entity);

        $service = new TaskQueueService($container, $repository, ['stub' => $type]);

        // WHEN
        $service->addTask($task);
    }

    public function testShouldExecuteTask()
    {
        // GIVEN
        $entity = new QueuedTask();
        /** @var \PHPUnit_Framework_MockObject_MockObject|StubTask $task */
        $task = $this->getMockBuilder('Mobillogix\QueueBundle\Tests\StubTask')
            ->setConstructorArgs([['some data'], $entity])
            ->setMethods(['execute'])->getMock();

        $type = StubTask::getConfigType();

        $entity->setType($task->getType())
            ->setData($task->getData())
            ->setPriority($type->getPriority());

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $task->expects($this->once())->method('execute')
            ->willReturnCallback(function($container, TaskLoggerInterface $logger) use ($task) {
                $logger->log($task, "Some log message");

                echo "Some raw output";
            });

        $repository->expects($this->once())
            ->method('setTaskStarted')->with($entity);

        $expectedEntity = clone $entity;
        $expectedEntity->setState($entity::STATE_DONE)
            ->setFinishedAt(new \DateTime())
            ->setLog("[message]: Some log message
---
[info]: Some raw output
---
");

        $repository->expects($this->once())
            ->method('saveQueuedTask')->with($expectedEntity);

        $service = new TaskQueueService($container, $repository, ['stub' => $type]);

        // WHEN
        $service->executeTask($task);
    }

    public function testShouldLogError()
    {
        // GIVEN
        $entity = new QueuedTask();
        /** @var \PHPUnit_Framework_MockObject_MockObject|StubTask $task */
        $task = $this->getMockBuilder('Mobillogix\QueueBundle\Tests\StubTask')
            ->setConstructorArgs([['some data'], $entity])
            ->setMethods(['execute'])->getMock();

        $type = StubTask::getConfigType();

        $entity->setType($task->getType())
            ->setData($task->getData())
            ->setPriority($type->getPriority());

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $task->expects($this->once())->method('execute')
            ->willReturnCallback(function() {
                echo "Some output";

                throw new TaskExecutionException("Some exception raised");
            });

        $expectedEntity = clone $entity;
        $expectedEntity->setState($entity::STATE_FAIL)
            ->setFinishedAt(new \DateTime())
            ->setLog('[info]: Some output
---
[error]: [Mobillogix\QueueBundle\Exception\TaskExecutionException]: Some exception raised
---
');

        $repository->expects($this->once())
            ->method('saveQueuedTask')->with($expectedEntity);

        $service = new TaskQueueService($container, $repository, ['stub' => $type]);

        // WHEN
        $service->executeTask($task);
    }

    public function testShouldMapEntityToTask()
    {
        // GIVEN
        $type = StubTask::getConfigType();
        $entity = new StubQueuedTask(1, 'stub', ['some data']);

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $service = new TaskQueueService($container, $repository, ['stub' => $type]);

        // WHEN
        $task = $service->mapEntityToTask($entity);

        $expected = new StubTask(['some data'], $entity);
        $this->assertEquals($expected, $task);
    }

    /**
     * @expectedException \Mobillogix\QueueBundle\Exception\TaskAddException
     * @expectedExceptionMessage Type 'stub' not found
     */
    public function testShouldThrowExceptionOnMap()
    {
        // GIVEN
        StubTask::getConfigType();
        $entity = new StubQueuedTask(1, 'stub', ['some data']);

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $service = new TaskQueueService($container, $repository, []);

        // WHEN
        $service->mapEntityToTask($entity);
    }

    /**
     * @expectedException \Mobillogix\QueueBundle\Exception\TaskAddException
     * @expectedExceptionMessage Task class 'Mobillogix\QueueBundle\Tests\Service\TaskQueueServiceTest' must be subclass of Mobillogix\QueueBundle\Model\BaseTask
     */
    public function testShouldThrowExceptionInvalidSuperclass()
    {
        // GIVEN
        $type = new ConfigQueuedTaskType('stub', __CLASS__, 0);
        $entity = new StubQueuedTask(1, 'stub', ['some data']);

        $container = new Container();
        $repository = $this->getQueuedTypesRepositoryMock();

        $service = new TaskQueueService($container, $repository, ['stub' => $type]);

        // WHEN
        $service->mapEntityToTask($entity);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueuedTaskRepository
     */
    private function getQueuedTypesRepositoryMock()
    {
        $repository = $this->getMockBuilder('Mobillogix\QueueBundle\Entity\QueuedTaskRepository')
            ->setMethods(['saveQueuedTask', 'setTaskStarted', 'setTaskFinished', 'getQueuedTasksForRun'])
            ->disableOriginalConstructor()->getMock();
        return $repository;
    }

}
