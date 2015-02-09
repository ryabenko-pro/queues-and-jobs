<?php

namespace Mobillogix\Launchpad\JobsBundle\Model;


use Mobillogix\Launchpad\JobsBundle\Entity\JobPackage;
use Mobillogix\Launchpad\JobsBundle\Interfaces\ProcessExecutorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseProcess
{

    protected $packages;

    /** @var JobPackage */
    protected $entity;

    final public function __construct($packages)
    {
        $this->packages = $packages;
    }

    /**
     * @return JobPackage
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param JobPackage $entity
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function execute(ProcessExecutorInterface $executor, ContainerInterface $container)
    {
        $counter = 0;
        $this->beforeExecute($container);

        foreach ((array)$this->packages as $package) {
            $executor->updatePackageNumber($this, ++$counter);

            try {
                ob_start();
                $this->doExecute($package, $container);

                $this->logOutput($executor, $counter);
            } catch (\PHPUnit_Framework_ExpectationFailedException $exception) {
                $this->logOutput($executor, $counter);

                // For unit tests only
                throw $exception;
            } catch (\Exception $exception) {
                $this->logOutput($executor, $counter);

                $executor->addError($this,
                    $counter,
                    sprintf("Error while executing task: '%s'", $exception->getMessage())
                );
            }
        }
    }

    protected function beforeExecute(ContainerInterface $container)
    {

    }

    abstract protected function doExecute($package, ContainerInterface $container);

    /**
     * @param ProcessExecutorInterface $executor
     * @param $counter
     */
    protected function logOutput(ProcessExecutorInterface $executor, $counter)
    {
        $output = ob_get_contents();
        $output = trim($output);
        ob_end_clean();

        if (!empty($output)) {
            $executor->addLog($this, $output, 0, $counter);
        }
    }

}