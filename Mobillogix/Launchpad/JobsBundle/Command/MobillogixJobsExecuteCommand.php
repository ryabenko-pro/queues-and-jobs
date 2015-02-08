<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;


use Mobillogix\Launchpad\JobsBundle\Service\ProcessExecutorService;
use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsExecuteCommand extends BaseSingleCommand
{
    /** @var ProcessExecutorService */
    protected $service;

    protected function configure()
    {
        $this->setName("mobillogix:jobs:execute")
            ->setDescription("Run packages. Can be run in parallel.");
    }

    protected function beforeStart()
    {
        $this->service = $this->getContainer()->get('process_executor.service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $this->service->executeProcesses();
    }

}