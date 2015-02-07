<?php


namespace Mobillogix\QueueBundle\Model;


use Mobillogix\QueueBundle\Entity\QueuedTask;
use Mobillogix\QueueBundle\Exception\TaskAddException;
use Mobillogix\QueueBundle\Service\TaskLoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseTask
{

    protected $data;

    /** @var QueuedTask */
    protected $entity;

    /**
     * @param null $data
     * @param QueuedTask $entity
     */
    final public function __construct($data = null, QueuedTask $entity = null)
    {
        $this->validateData($data);

        $this->data = $data;
        $this->entity = $entity;
    }

    /**
     * @param string|null $param Param name to return
     * @return mixed
     */
    public function getData($param = null)
    {
        if (is_null($param)) {
            return $this->data;
        }

        if (array_key_exists($param, $this->data)) {
            return $this->data[$param];
        }

        return null;
    }

    /**
     * @param null $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return QueuedTask
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param QueuedTask $entity
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Check data provided. Should trow exception, if data not valid.
     *
     * @param array $data
     * @throws TaskAddException
     */
    public function validateData($data) { }

    /**
     * Before task added into queue.
     * !WARNING! this method and `execute` very likely to be executed in different context.
     * Do not make execute method depended on preExecute
     *
     * @param ContainerInterface $container
     * @param TaskLoggerInterface $logger
     * @return mixed
     */
    public function beforeAdd(ContainerInterface $container, TaskLoggerInterface $logger) { }

    /**
     * Run task
     *
     * @param ContainerInterface $container
     * @param TaskLoggerInterface $logger
     * @return mixed
     */
    abstract public function execute(ContainerInterface $container, TaskLoggerInterface $logger);

    /**
     * Returns the type of task to be executed
     * @return string
     */
    abstract public function getType();

}