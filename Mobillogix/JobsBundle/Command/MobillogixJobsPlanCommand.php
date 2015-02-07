<?php

namespace Mobillogix\JobsBundle\Command;


use Mobillogix\JobsBundle\Service\JobPlannerService;
use Mobillogix\Common\Command\BaseSingleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsPlanCommand extends BaseSingleCommand
{
    /** @var JobPlannerService */
    protected $service;

    protected function configure()
    {
        $this->setName("mobillogix:jobs:plan")
            ->setDescription("Run jobs planning to create packages");
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
        $this->service->runPlanning();
    }

}