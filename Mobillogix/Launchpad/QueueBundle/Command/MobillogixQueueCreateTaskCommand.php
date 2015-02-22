<?php

namespace Mobillogix\Launchpad\QueueBundle\Command;


use Mobillogix\Launchpad\QueueBundle\DependencyInjection\Util\ConfigQueuedTaskType;
use Mobillogix\Launchpad\QueueBundle\Model\AbstractTask;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixQueueCreateTaskCommand extends ContainerAwareCommand
{

    const BASE_TASK = 'Mobillogix\Launchpad\QueueBundle\Model\BaseTask';

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
        $typeName = $input->getArgument('type');
        $data = (array)@json_decode($input->getArgument('data'));


        $types = $this->getContainer()->getParameter('mobillogix_launchpad.queue.task_types');

        if (!isset($types[$typeName])) {
            throw new \Exception("Type '{$typeName}' not found.");
        }

        $type = $types[$typeName];
        $type = new ConfigQueuedTaskType($type['name'], $type['class_name'], $type['priority']);

        $class = $type->getClassName();
        if (!is_subclass_of($class, self::BASE_TASK)) {
            throw new \Exception("Task class '{$class}' must be subclass of " . self::BASE_TASK);
        }

        $task = new $class($data);

        $service = $this->getContainer()->get('mobillogix_launchpad.queue.task_queue.service_database');
        $id = $service->addTask($task);

        $output->writeln("Task #{$id} with type '{$typeName}' added.");
    }

}