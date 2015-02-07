<?php

namespace Mobillogix\QueueBundle\Tests;


use Mobillogix\QueueBundle\Entity\QueuedTask;

class StubQueuedTask extends QueuedTask
{

    function __construct($id, $type = null, $data = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
    }

}