<?php

namespace Mobillogix\Launchpad\QueueBundle\Controller;

use Mobillogix\Launchpad\QueueBundle\Entity\QueuedTask;
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
        $types = $this->container->getParameter('mobillogix_launchpad.queue.task_types');

        $qb = $this->get('mobillogix_launchpad.queue.repository.queued_task')->createQueryBuilder("qt");

        $qb->orderBy("qt.createdAt", "DESC");

        $type = $request->get('type');
        if ($type) {
            $qb->andWhere("qt.type = :type")
                ->setParameter('type', $type);
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage(max(1, (integer) $request->query->get('page', 1)));
        $pager->setMaxPerPage(max(5, min(50, (integer) $request->query->get('per_page', 20))));

        $processes = $this->getRunningQueues();

        return [
            'base_template' => $this->container->getParameter('mobillogix_launchpad.queue.base_template'),
            'types' => $types,
            'type_filter'  => $type,
            'tasks'  => $pager,
            'processes' => $processes,
        ];
    }

    /**
     * @param Request $request
     * @param QueuedTask $task
     */
    public function suspendAction(Request $request, QueuedTask $task)
    {
        if ($task->isCancelled() || $task->isRunning()) {
            $request->getSession()->getFlashBag()->add('error', 'Task cannot be set to waiting.');

            return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
        }
        $task->dropState();
        $task->setState(QueuedTask::STATE_WAITING);
        $this->container->get('mobillogix_launchpad.queue.repository.queued_task')->saveQueuedTask($task);
        $request->getSession()->getFlashBag()->add('warning', sprintf('Task #%s was set to waiting', $task->getId()));


        return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
    }

    /**
     * @param Request $request
     * @param QueuedTask $task
     */
    public function cancelAction(Request $request, QueuedTask $task)
    {
        if ($task->isCancelled() || $task->isRunning()) {
            $request->getSession()->getFlashBag()->add('error', 'Task cannot be cancelled.');

            return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
        }
        $task->dropState();
        $task->setState(QueuedTask::STATE_CANCELLED);
        $this->container->get('mobillogix_launchpad.queue.repository.queued_task')->saveQueuedTask($task);
        $request->getSession()->getFlashBag()->add('warning', sprintf('Task #%s was cancelled', $task->getId()));


        return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
    }

    /**
     * @param Request $request
     * @param QueuedTask $task
     */
    public function retryAction(Request $request, QueuedTask $task)
    {
        if ($task->isCancelled() || $task->isRunning()) {
            $request->getSession()->getFlashBag()->add('error', 'Task cannot be added to queue.');

            return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
        }
        $task->dropState();
        $this->container->get('mobillogix_launchpad.queue.repository.queued_task')->saveQueuedTask($task);

        $service = $this->container->get('mobillogix_launchpad.queue.task_queue.service_database');
        $service->addTask($service->mapEntityToTask($task));
        $request->getSession()->getFlashBag()->add('warning', sprintf('Task #%s was added to queue', $task->getId()));


        return $this->redirect($this->generateUrl('mobillogix_queue_task_index'));
    }

    /**
     * @Template()
     * @param QueuedTask $task
     * @return array
     */
    public function getAction(QueuedTask $task)
    {
        return [
            'base_template' => $this->container->getParameter('mobillogix_launchpad.queue.base_template'),
            'task'  => $task,
        ];
    }

    private function getRunningQueues()
    {
        $c = $this->container;

        $execGrep = $c->getParameter('cmd_grep');
        $execPs = $c->getParameter('cmd_ps');
        $execAwk = $c->getParameter('cmd_awk');
        $env = $c->getParameter('kernel.environment');

        $processes = [];
        $rootDir = realpath($c->getParameter('kernel.root_dir') . "/../");
        $cmd = "{$execPs} aux | {$execGrep} {$env} | {$execGrep} {$rootDir} | {$execGrep} \\:queue | {$execGrep} -v \"/bin/sh\" |  {$execAwk} '{print $13}'";

        exec($cmd, $processes);

        return $processes;
    }

}
