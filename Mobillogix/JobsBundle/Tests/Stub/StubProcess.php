<?php

namespace Mobillogix\JobsBundle\Tests\Stub;


use Mobillogix\JobsBundle\Model\BaseProcess;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StubProcess extends BaseProcess
{

    protected function doExecute($package, ContainerInterface $container)
    {

    }
}