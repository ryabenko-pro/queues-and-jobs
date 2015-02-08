<?php

namespace Mobillogix\Launchpad\QueueBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/queue")
 */
class QueueController extends Controller
{
    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $types = $this->container->getParameter('mobillogix_queue.task_types');

        $qb = $this->get('mobillogix_queue.repository.queued_task')->createQueryBuilder("qt");

        $type = $request->get('type');
        if ($type) {
            $qb->andWhere("qt.type = :type")
                ->setParameter('type', $type);
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage(max(1, (integer) $request->query->get('page', 1)));
        $pager->setMaxPerPage(max(5, min(50, (integer) $request->query->get('per_page', 20))));

        return [
            'base_template' => $this->container->getParameter('mobillogix_queue.base_template'),
            'types' => $types,
            'type_filter'  => $type,
            'tasks'  => $pager,
        ];
    }
}
