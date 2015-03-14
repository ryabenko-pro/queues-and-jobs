<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;


use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Mobillogix\Launchpad\JobsBundle\Service\JobPlannerService;
use Mobillogix\Launchpad\JobsBundle\Service\ProcessExecutorService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsDevFullCommand extends BaseSingleCommand
{
    protected $symbols;

    /** @var JobPlannerService */
    private $planner;
    /** @var ProcessExecutorService */
    protected $executor;


    protected function configure()
    {
        $this->setName("mobillogix:jobs:dev-full")
            ->setDescription("Plan, execute, finish, repeat. For development purposes only!");
    }

    protected function beforeStart()
    {
        $this->planner = $this->getContainer()->get('job_planner.service');
        $this->executor = $this->getContainer()->get('process_executor.service');

        $this->symbols = ['—', '\\', '|', '/'];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $this->planner->runPlanning();
        $this->executor->executeProcesses();
        $this->planner->runFinishing($this->getContainer());

        $output->write("\r" . $this->symbols[$this->cycles % count($this->symbols)]);
    }

}