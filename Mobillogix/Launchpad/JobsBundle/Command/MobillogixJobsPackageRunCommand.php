<?php

namespace Mobillogix\Launchpad\JobsBundle\Command;


use Mobillogix\Launchpad\JobsBundle\Interfaces\ProcessExecutorInterface;
use Mobillogix\Launchpad\JobsBundle\Model\BaseProcess;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MobillogixJobsPackageRunCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName("mobillogix:jobs:package-run")
            ->setDescription("Execute single package in any state. Does not change state and counts.");
        $this->addArgument('id', InputArgument::REQUIRED, "Package id to run");
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

        $slug = $process->getEntity()->getJob()->getJobType()->getSlug();
        $output->writeln(sprintf("Executing process #%d of type '%s'", $id, $slug));

        $process->execute(new StubExecutor($output), $this->getContainer());

        $output->writeln("Finished.");
    }

}

class StubExecutor implements ProcessExecutorInterface
{

    /** @var OutputInterface */
    protected $output;

    function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Executes process
     * @param BaseProcess $process
     * @return mixed
     */
    public function runProcess(BaseProcess $process)
    {
    }

    /**
     * Save error information
     *
     * @param BaseProcess $process
     * @param $currentPackage
     * @param $error
     * @return mixed
     */
    public function addError(BaseProcess $process, $currentPackage, $error)
    {
        $this->output->writeln("# Error: " . $error);
    }

    /**
     * Update current package number
     *
     * @param BaseProcess $process
     * @param $number
     * @return mixed
     */
    public function updatePackageNumber(BaseProcess $process, $number)
    {
        $this->output->writeln("Executing {$number} process.");
    }

    /**
     * @inheritdoc
     */
    public function addLog(BaseProcess $process, $message, $currentPackage = 0)
    {
        $this->output->writeln("Process log: " . $message);
    }
}