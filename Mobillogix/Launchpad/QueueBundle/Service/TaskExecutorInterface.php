<?php


namespace Mobillogix\Launchpad\QueueBundle\Service;


use Mobillogix\Launchpad\QueueBundle\Model\BaseTask;

interface TaskExecutorInterface
{

    /**
     * Executes task
     *
     * @param BaseTask $task
     */
    public function addTask(BaseTask $task);

}