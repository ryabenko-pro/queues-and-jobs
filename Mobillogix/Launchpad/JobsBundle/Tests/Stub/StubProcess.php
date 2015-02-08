<?php

namespace Mobillogix\Launchpad\JobsBundle\Tests\Stub;


use Mobillogix\Launchpad\JobsBundle\Model\BaseProcess;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StubProcess extends BaseProcess
{

    protected function doExecute($package, ContainerInterface $container)
    {

    }
}