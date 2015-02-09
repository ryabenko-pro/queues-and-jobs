<?php


namespace Mobillogix\Launchpad\JobsBundle\Controller;


use Mobillogix\Launchpad\JobsBundle\Entity\Job;
use Mobillogix\Launchpad\JobsBundle\Entity\JobEvent;
use Mobillogix\Launchpad\JobsBundle\Entity\JobPackage;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ml-jobs")
 */
class JobsController extends Controller
{

    /**
     * @Route("/", name="jobs_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $qb = $this->get('mobillogix.catalogue.repository.job')->getListQueryBuilder();

        $type = $request->get('type');
        if ($type) {
            $qb->andWhere("t.slug = :slug")
                ->setParameter('slug', $type);
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage(max(1, (integer) $request->query->get('page', 1)));
        $pager->setMaxPerPage(max(5, min(50, (integer) $request->query->get('per_page', 20))));

        $response = [
            'type_filter' => $type,
            'jobs' => $pager,
        ];

        return $this->buildResponse($response);
    }

    /**
     * Show packages
     * @Route("/{id}", name="jobs_show", requirements={"id"="\d+"})
     * @Template("MobillogixLaunchpadJobsBundle:Jobs:packages.html.twig")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id, Request $request)
    {
        $job = $this->getJobOr404($id);

        $packages = $this->get('mobillogix.catalogue.repository.job_package')->getPackagesQueryBuilder($job);

        $pager = new Pagerfanta(new DoctrineORMAdapter($packages));
        $pager->setCurrentPage(max(1, (integer) $request->query->get('page', 1)));
        $pager->setMaxPerPage(max(5, min(50, (integer) $request->query->get('per_page', 50))));

        /** @var JobPackage[] $pager */
        return $this->buildResponse([
            'job'   => $job,
            'packages'  => $pager,
        ]);
    }

    /**
     * @Route("/{id}/events", name="jobs_show_events")
     * @Template()
     */
    public function eventsAction($id, Request $request)
    {
        $job = $this->getJobOr404($id);

        $jobEventRepository = $this->get('mobillogix.catalogue.repository.job_event');

        $events = $jobEventRepository->getEventsQueryBuilder($job);
        $eventTypes = $jobEventRepository->getEventsTypes($job);

        $type = $request->get('type');
        if ($type) {
            $events->andWhere("e.type = :slug")
                ->setParameter('slug', $type);
        }
        
        $packageId = $request->get('package_id');
        if ($packageId) {
            $events->andWhere("e.jobPackage = :package_id")
                ->setParameter('package_id', $packageId);
        }
        

        $pager = new Pagerfanta(new DoctrineORMAdapter($events));
        $pager->setCurrentPage(max(1, (integer) $request->query->get('page', 1)));
        $pager->setMaxPerPage(max(5, min(50, (integer) $request->query->get('per_page', 50))));

        /** @var JobEvent[] $pager */
        return $this->buildResponse([
            'job'   => $job,
            'events'  => $pager,
            'event_types'   => $eventTypes,
            'type_filter' => $type,
            'package_id' => $packageId,
        ]);
    }

    /**
     * @param $id
     * @return Job
     */
    protected function getJobOr404($id)
    {
        $job = $this->get('mobillogix.catalogue.repository.job')->getJobSummary($id);

        if (!$job) {
            throw $this->createNotFoundException("Job {$id} not found.");
        }

        return $job;
    }

    /**
     * @param $response
     * @return mixed
     */
    protected function buildResponse($response)
    {
        $types = $this->get('mobillogix.catalogue.repository.job_type')->findAll();

        $response['types'] = $types;
        $response['base_template'] = $this->container->getParameter('mobillogix_jobs.base_template');

        return $response;
    }

}