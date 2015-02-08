<?php


namespace Mobillogix\Launchpad\QueueBundle\Model;


use Mobillogix\Launchpad\QueueBundle\Exception\TaskExecutionException;
use Mobillogix\Launchpad\QueueBundle\Service\TaskLoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractTask extends BaseTask
{

    protected $type;

    /**
     * @param $type
     * @param $data
     * @return AbstractTask
     */
    public static function create($type, $data) {
        $task = new AbstractTask($data);
        $task->type = $type;

        return $task;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContainerInterface $container, TaskLoggerInterface $logger)
    {
        throw new TaskExecutionException("This task should not be executed. Use it for creation purposes only.");
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }
}