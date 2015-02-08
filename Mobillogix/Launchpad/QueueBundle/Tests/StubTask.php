<?php


namespace Mobillogix\Launchpad\QueueBundle\Tests;


use Mobillogix\Launchpad\QueueBundle\DependencyInjection\Util\ConfigQueuedTaskType;
use Mobillogix\Launchpad\QueueBundle\Model\BaseTask;
use Mobillogix\Launchpad\QueueBundle\Service\TaskLoggerInterface;
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