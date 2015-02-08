<?php

namespace Mobillogix\Launchpad\JobsBundle\Repository;

use Mobillogix\Launchpad\JobsBundle\Entity\JobType;
use Doctrine\ORM\EntityRepository;

/**
 * @method JobType findAll
 */
class JobTypeRepository extends EntityRepository
{

    public function saveJobType(JobType $type)
    {
        $em = $this->getEntityManager();
        $em->persist($type);
        $em->flush($type);
    }

}
