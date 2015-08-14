<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;

use Mobillogix\Launchpad\JobsBundle\Service\Persistence\JobProcessPersistenceService;
use Mobillogix\Launchpad\JobsBundle\Service\ProcessExecutorService;
use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsExecuteCommand extends BaseSingleCommand
{
    /** @var  JobProcessPersistenceService */
    protected $service;

    protected function configure()
    {
        $this->setName("mobillogix:jobs:execute")
            ->setDescription("Run packages. Can be run in parallel.");
    }

    protected function beforeStart()
    {
        $this->service = $this->getContainer()->get('job_process_persistence.service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $processes = $this->service->getProcessesForRun();

        foreach ($processes as $process) {
            $this->runAsProcess('mobillogix:jobs:package-run', [
                $process->getEntity()->getId(),
            ]);
        }
    }

}
