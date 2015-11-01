<?php


namespace Mobillogix\Launchpad\QueueBundle\Tests;


use Mobillogix\Launchpad\QueueBundle\Model\BaseTask;
use Mobillogix\Launchpad\QueueBundle\Service\TaskLoggerInterface;

class StubTaskLogger implements TaskLoggerInterface
{

    public $logs = [];

    /**
     * @inheritdoc
     */
    public function log(BaseTask $task, $message, $type = self::LOG_MESSAGE)
    {
        $this->logs[] = sprintf('%s [%s]: %s', date('Y-m-d H:i:s'), $type, $message);
    }
}