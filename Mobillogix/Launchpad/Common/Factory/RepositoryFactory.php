<?php

namespace Mobillogix\Launchpad\Common\Factory;


use Doctrine\ORM\EntityManager;

class RepositoryFactory
{

    /** @var EntityManager */
    protected $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Create repository for entity
     * @param $entity
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entity)
    {
        return $this->em->getRepository($entity);
    }

}