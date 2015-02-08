<?php

namespace Mobillogix\Launchpad\QueueBundle\Command;


use Mobillogix\Launchpad\QueueBundle\Model\AbstractTask;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixQueueCreateTaskCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName("mobillogix:queue:create-task");
        $this->addArgument("type", InputArgument::REQUIRED, "Task type")
            ->addArgument("data", InputArgument::OPTIONAL, "Task data in json string (use quotes). Empty array by default.", '{}');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $data = (array)@json_decode($input->getArgument('data'));

        $service = $this->getContainer()->get('mobillogix_queue.task_queue.service');

        $id = $service->addTask(AbstractTask::create($type, $data));

        $output->writeln("Task #{$id} with type '{$type}' added.");
    }

}