<?php


namespace Mobillogix\QueueBundle\Tests;


use Mobillogix\QueueBundle\Model\BaseTask;
use Mobillogix\QueueBundle\Service\TaskLoggerInterface;

class StubTaskLogger implements TaskLoggerInterface
{

    /**
     * @inheritdoc
     */
    public function log(BaseTask $task, $message, $type = self::LOG_MESSAGE)
    {

    }
}