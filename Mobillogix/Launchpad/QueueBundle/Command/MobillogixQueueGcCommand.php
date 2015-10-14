<?php

namespace Mobillogix\Launchpad\QueueBundle\Command;

use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Mobillogix\Launchpad\QueueBundle\Repository\QueuedTaskRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixQueueGcCommand extends BaseSingleCommand
{
    /** @var QueuedTaskRepository */
    protected $repository;

    protected function configure()
    {
        $this->setName("mobillogix:queue:gc");
        $this->setDescription("Garbage collector for tasks. Rules are:\n - 'selected' for some time.\n - started and not finished for some time.");
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeStart(InputInterface $input, OutputInterface $output)
    {
        $this->repository = $this->getContainer()->get('mobillogix_launchpad.queue.repository.queued_task');
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
    }

}