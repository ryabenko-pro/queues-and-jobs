<?php

namespace Mobillogix\JobsBundle\Command;


use Mobillogix\JobsBundle\Service\JobPlannerService;
use Mobillogix\Common\Command\BaseSingleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsFinishCommand extends BaseSingleCommand
{
    /** @var JobPlannerService */
    protected $service;

    protected function configure()
    {
        $this->setName("mobillogix:jobs:finish");
    }

    protected function beforeStart()
    {
        $this->service = $this->getContainer()->get('job_planner.service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $this->service->runFinishing($this->getContainer());
    }

}