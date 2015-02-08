<?php


namespace Mobillogix\Launchpad\QueueBundle\Tests;


use Mobillogix\Launchpad\QueueBundle\Model\BaseTask;
use Mobillogix\Launchpad\QueueBundle\Service\TaskLoggerInterface;

class StubTaskLogger implements TaskLoggerInterface
{

    /**
     * @inheritdoc
     */
    public function log(BaseTask $task, $message, $type = self::LOG_MESSAGE)
    {

    }
}