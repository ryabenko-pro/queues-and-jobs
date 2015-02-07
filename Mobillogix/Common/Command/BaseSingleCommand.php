<?php

namespace Mobillogix\Common\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->addOption("single-id", "sid", InputOption::VALUE_OPTIONAL, "Only one task with same name + single-id is allowed.", "")
            ->addOption("permanent", "p", InputOption::VALUE_NONE, "Should this task do cycles. If not present it will be run only once, as usual command.")
            ->addOption("pid-dir", "pid", InputOption::VALUE_OPTIONAL, "Directory name to store pid files.", null)
            ->addOption("cycle-delay", "del", InputOption::VALUE_OPTIONAL, "Delay between cycles in seconds.", 1)
            ->addOption("memory-limit", "mem", InputOption::VALUE_OPTIONAL, "Task will gentle exit when limit reached.")
            ->addOption("cycles-limit", "cyc", InputOption::VALUE_OPTIONAL, "Task will gentle exit after cycles done.", 10000);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
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

        file_put_contents($this->getPidFilename(), getmypid());

        $this->memoryLimit = $input->getOption('memory-limit');
        $this->cyclesLimit = intval($input->getOption('cycles-limit'));
        $this->cycleDelay = intval($input->getOption('cycle-delay'));

        $this->beforeStart();

        $lastStartTs = time();
        $this->doExecute($input, $output);

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
        $pidFilename = $this->getPidFilename($this->input);
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

        $pidFilename = $pidDir . "/" . sprintf("%s-%s.pid", $this->getName(), $this->input->getOption("single-id"));

        return $pidFilename;
    }

    /**
     * @param $pid
     * @return bool
     */
    private function isAlive($pid)
    {
        return file_exists("/proc/{$pid}");
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
     */
    protected function beforeStart()
    {

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    abstract public function doExecute(InputInterface $input, OutputInterface $output);

}