<?php

namespace Mobillogix\Launchpad\QueueBundle\Command;

use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Mobillogix\Launchpad\QueueBundle\Repository\QueuedTaskRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mobillogix\Launchpad\QueueBundle\Entity\QueuedTask;

class MobillogixQueueGcCommand extends BaseSingleCommand
{
    /** @var QueuedTaskRepository */
    protected $repository;

    protected $em;

    protected function configure()
    {
        $this->setName("mobillogix:queue:gc");
        $this->setDescription("Garbage collector for tasks. Rules are:\n".
            " - 'selected' for some time.\n".
            " - started and not finished for some time.\n".
            " - 'running' but process is dead.");
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeStart(InputInterface $input, OutputInterface $output)
    {
        $this->repository = $this->getContainer()->get('mobillogix_launchpad.queue.repository.queued_task');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->repository->runGc();

        $output->writeln("{$count} tasks was collected");

        $tasks = $this->repository->findBy(['state' => QueuedTask::STATE_RUN]);
        foreach ($tasks as $task) {
            $alive = false;
            if ($pid = $task->getPid()) {
                $alive = $this->isAlive($pid);
            }
            if ($task->getStartedAt() && $alive == false) {
                if ($input->getOption('verbose')) {
                    echo "Setting queued task {$task->getId()} state as " . QueuedTask::STATE_FAIL . "\n";
                }
                $task->setState(QueuedTask::STATE_FAIL);
                $this->em->persist($task);
            }
            $this->em->flush();
        }

    }

}