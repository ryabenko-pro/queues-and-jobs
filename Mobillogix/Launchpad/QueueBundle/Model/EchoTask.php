<?php


namespace Mobillogix\Launchpad\QueueBundle\Model;


use Mobillogix\Launchpad\QueueBundle\Service\TaskLoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EchoTask extends BaseTask
{

    /**
     * @inheritdoc
     */
    public function execute(ContainerInterface $container, TaskLoggerInterface $logger)
    {
        print_r($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'echo';
    }
}