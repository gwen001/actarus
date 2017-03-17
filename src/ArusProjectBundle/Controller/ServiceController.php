<?php

namespace ArusProjectBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusProjectBundle\Entity\ArusProject;
use ArusEntityTaskBundle\Entity\ArusEntityTask;


class ServiceController extends Controller
{
	protected $em;

	protected $container;

	protected $router;

	protected $formFactory;

	protected $templating;


	public function __construct( $entityManager, $container, $router, $formFactory, $templating ) {
		$this->em          = $entityManager;
		$this->container   = $container;
		$this->router      = $router;
		$this->formFactory = $formFactory;
		$this->templating  = $templating;
	}


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$t_project = $this->em->getRepository('ArusProjectBundle:ArusProject')->search( $data, $offset, $limit );
		if( is_array($t_project) ) {
			foreach ($t_project as $p) {
				$this->get('app')->getRelatedEntityObject($p, true);
				$n = $this->get('server')->search( ['project'=>$p], -1 );
				$p->setServers( array_fill(0,$n,null) );
				$n = $this->get('domain')->search( ['project'=>$p], -1 );
				$p->setDomains( array_fill(0,$n,null) );
				$n = $this->get('host')->search( ['project'=>$p], -1 );
				$p->setHosts( array_fill(0,$n,null) );
				$n = $this->get('entity_alert')->search( ['project'=>$p], -1 );
				$p->setAlerts( array_fill(0,$n,null) );
				$n = $this->get('entity_task')->search( ['project'=>$p], -1 );
				$p->setTasks( array_fill(0,$n,null) );
			}
		}

		return $t_project;
	}


	public function exist( $name, $id=null, $return_object=false )
	{
		$t_params = ['name'=>[$name,'=']];

		if( $id ) {
			$t_params['id'] = [$id,'!='];
		}

		if( $return_object ) {
			$offset = 1;
		} else {
			$offset = -1;
		}

		$exist = $this->search( $t_params, $offset );

		if( !$return_object ) {
			return $exist;
		} elseif( is_array($exist) && count($exist) ) {
			return $exist[0];
		} else {
			return false;
		}
	}


	public function create( $name, $recon=true )
	{
		$project = new ArusProject();
		$project->setName( $name, true );

		$em = $this->em;
		$em->persist( $project );
		$em->flush( $project );

		if( $recon ) {
			$this->get('app')->recon( $project, 'project' );
		}

		return $project;
	}
}
