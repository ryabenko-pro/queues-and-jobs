<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;


use Mobillogix\Launchpad\JobsBundle\Service\JobPlannerService;
use Mobillogix\Launchpad\Common\Command\BaseSingleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsStopCommand extends BaseSingleCommand
{
    protected $needToCheckStopFile = false;

    protected function configure()
    {
        $this->setName("mobillogix:jobs:stop")
            ->setDescription("Stop all jobs");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \Exception
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $stopFileName = $this->getStopFileName();
        if(!file_exists($stopFileName)) {
            if (false === file_put_contents($stopFileName, '')) {
                throw new \Exception("Can't write stop file '{$stopFileName}'");
            }
        } else {
            $output->write('<info>Stop file \'' . $stopFileName . '\' already exists</info>');
        }
    }

}