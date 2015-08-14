<?php


namespace Mobillogix\Launchpad\QueueBundle\Service;


use Mobillogix\Launchpad\QueueBundle\Model\BaseTask;

interface TaskExecutorInterface
{

    /**
     * Executes task. If $parent is present, do not execute $task until parent not finished
     *
     * @param BaseTask $task
     * @param BaseTask $parent
     * @return
     */
    public function addTask(BaseTask $task, BaseTask $parent = null);

}