<?php

namespace Mobillogix\Launchpad\Common\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

abstract class BaseSingleCommand extends ContainerAwareCommand
{

    const STOP_FILE = "jobs_stop";

    protected $cycles = 1;

    protected $memoryLimit;
    protected $cyclesLimit;

    /**
     * Delay in seconds between last and current cycles start
     */
    protected $cycleDelay;

    /** @var InputInterface */
    protected $input;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->addOption("single-id", null, InputOption::VALUE_OPTIONAL, "Only one task with same name + single-id is allowed.", "")
            ->addOption("permanent", "p", InputOption::VALUE_NONE, "Should this task do cycles. If not present it will be run only once, as usual command.")
            ->addOption("pid-dir", null, InputOption::VALUE_OPTIONAL, "Directory name to store pid files.", null)
            ->addOption("cycle-delay", null, InputOption::VALUE_OPTIONAL, "Delay between cycles in seconds.", 1)
            ->addOption("memory-limit", null, InputOption::VALUE_OPTIONAL, "Task will gentle exit when limit reached.")
            ->addOption("cycles-limit", null, InputOption::VALUE_OPTIONAL, "Task will gentle exit after cycles done.", 10000);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    final public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        if ($this->isInstanceRunning()) {
            return;
        }

        if ($this->isStopFileExists()) {
            $output->writeln("Stop file is present. Exiting.");

            return;
        }

        $pidFilename = $this->getPidFilename();
        if (false === file_put_contents($pidFilename, getmypid())) {
            throw new \Exception("Can't write pid file '{$pidFilename}'");
        }

        $this->memoryLimit = $input->getOption('memory-limit');
        $this->cyclesLimit = intval($input->getOption('cycles-limit'));
        $this->cycleDelay = intval($input->getOption('cycle-delay'));

        $this->beforeStart($input, $output);

        $lastStartTs = time();
        $this->doExecute($input, $output);
        $this->cycles++;

        if (!$input->getOption("permanent")) {
            return;
        }

        do {
            sleep(max(0, $this->cycleDelay - (time() - $lastStartTs)));

            $lastStartTs = time();
            $this->doExecute($input, $output);
            $this->cycles++;
        } while ($this->canContinue());
    }

    /**
     * @return bool
     */
    public function isInstanceRunning()
    {
        $pidFilename = $this->getPidFilename();
        if (is_readable($pidFilename)) {
            $pid = file_get_contents($pidFilename);

            return $this->isAlive($pid);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPidFilename()
    {
        $container = $this->getContainer();

        $pidDir = $this->input->getOption("pid-dir");
        if (is_null($pidDir)) {
            $pidDir = $container->getParameter('kernel.cache_dir');
        }

        // Win OS
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pidFilename = $pidDir . "/" . str_replace(":", "-", sprintf("%s-%s.pid", $this->getName(), $this->input->getOption("single-id")));
        } else {
            $pidFilename = $pidDir . "/" . sprintf("%s-%s.pid", $this->getName(), $this->input->getOption("single-id"));
        }

        return escapeshellarg($pidFilename);
    }

    /**
     * @param $pid
     * @return bool
     */
    protected function isAlive($pid)
    {
        return trim($pid) && (posix_getpgid($pid) !== false);
    }

    /**
     * @return bool
     */
    private function canContinue()
    {
        if ($this->cycles >= $this->cyclesLimit) {
            return false;
        }

        if (!is_null($this->memoryLimit) && memory_get_usage(true) > $this->memoryLimit) {
            return false;
        }

        if ($this->isStopFileExists()) {
            return false;
        }

        return true;
    }

    private function isStopFileExists()
    {
        return file_exists(sprintf("%s/%s", dirname($this->getPidFilename()), self::STOP_FILE));
    }

    /**
     * Do some initialization outside of process's loop
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function beforeStart(InputInterface $input, OutputInterface $output)
    {

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    abstract public function doExecute(InputInterface $input, OutputInterface $output);

    /**
     * Run existing command using Symfony's Process component.
     * @param string $command
     * @param array $args Command line arguments and options list
     */
    protected function runAsProcess($command, $args = [])
    {
        $container = $this->getContainer();

        $args = array_merge([
            sprintf('%s=%s', '--env', $container->get('kernel')->getEnvironment()),
        ], $args);

        $args = join(' ', $args);

        $dir = $this->getContainer()->getParameter('kernel.root_dir') . '/../';
        $process = new Process(sprintf('php %s/bin/console %s %s', $dir, $command, $args), null, null, null, null);
        $process->run();
    }

}