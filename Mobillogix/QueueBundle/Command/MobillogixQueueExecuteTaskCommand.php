<?php

namespace Mobillogix\QueueBundle\Command;

use Mobillogix\Common\Command\BaseSingleCommand;
use Mobillogix\QueueBundle\Service\TaskQueueService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixQueueExecuteTaskCommand extends BaseSingleCommand
{
    /** @var TaskQueueService */
    protected $service;

    protected function configure()
    {
        $this->setName("mobillogix:queue:execute-task")
            ->setDescription("Execute queued tasks. May be started in parallel several instances.")
            ->addArgument('ids', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'Run tasks with specific id, no matter its state is.');
    }

    protected function beforeStart()
    {
        $this->service = $this->getContainer()->get('mobillogix_common.task_queue.service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \Exception
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $ids = $input->getArgument('ids');

        if (empty($ids)) {
            $this->service->runTasks();
        } else {
            if ($input->getOption('permanent')) {
                throw new \Exception("Ids can not be used with 'permanent' option");
            }

            $entities = $this->getContainer()->get('mobillogix_common.repository.queued_task')->findByIds($ids);
            foreach ($entities as $entity) {
                $task = $this->service->mapEntityToTask($entity);
                $this->service->executeTask($task);
            }
        }
    }

}