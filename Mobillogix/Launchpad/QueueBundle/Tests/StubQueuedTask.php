<?php

namespace Mobillogix\Launchpad\QueueBundle\Tests;


use Mobillogix\Launchpad\QueueBundle\Entity\QueuedTask;

class StubQueuedTask extends QueuedTask
{

    function __construct($id, $type = null, $data = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
    }

}