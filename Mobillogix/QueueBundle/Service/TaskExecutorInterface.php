<?php


namespace Mobillogix\QueueBundle\Service;


use Mobillogix\QueueBundle\Model\BaseTask;

interface TaskExecutorInterface
{

    /**
     * Executes task
     *
     * @param BaseTask $task
     */
    public function addTask(BaseTask $task);

}