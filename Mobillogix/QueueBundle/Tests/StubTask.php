<?php


namespace Mobillogix\QueueBundle\Tests;


use Mobillogix\QueueBundle\DependencyInjection\Util\ConfigQueuedTaskType;
use Mobillogix\QueueBundle\Model\BaseTask;
use Mobillogix\QueueBundle\Service\TaskLoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StubTask extends BaseTask
{

    /**
     * @inheritdoc
     */
    public function execute(ContainerInterface $container, TaskLoggerInterface $logger) { }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'stub';
    }

    static public function getConfigType()
    {
        return new ConfigQueuedTaskType('Stub task', __CLASS__, 5);
    }

}