<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;


use Mobillogix\Launchpad\JobsBundle\Entity\JobPackage;
use Mobillogix\Launchpad\JobsBundle\Exception\MobillogixJobsException;
use Mobillogix\Launchpad\JobsBundle\Interfaces\ProcessExecutorInterface;
use Mobillogix\Launchpad\JobsBundle\Model\BaseProcess;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

class MobillogixJobsPackageRunCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName("mobillogix:jobs:package-run")
            ->setDescription("Execute single package in 'STATUS_SELECTED' state only.");
        $this->addArgument('id', InputArgument::REQUIRED, "Package id to run");
        $this->addOption('force', 'f', InputOption::VALUE_NONE, "Force executing package in any state");
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->getContainer()->get('process_executor.service');

        $id = $input->getArgument("id");
        $process = $service->getProcess($id);

        if ($process->getEntity()->getStatus() !== JobPackage::STATUS_SELECTED && !$input->getOption("force")) {
            throw new MobillogixJobsException(sprintf("Package status '%s' not allowed for execution (must be '" . JobPackage::STATUS_SELECTED . "').", $process->getEntity()->getStatus()));
        }

        $slug = $process->getEntity()->getJob()->getJobType()->getSlug();
        $output->writeln(sprintf("Executing process #%d of type '%s'", $id, $slug));

        $service->runProcess($process);

        $output->writeln("Finished.");
    }

}
