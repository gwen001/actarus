<?php

namespace ArusBucketBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Collection;

use ArusBucketBundle\Entity\ArusBucket;
use ArusEntityTaskBundle\Entity\ArusEntityTask;

use Actarus\Utils;


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

		$t_bucket = $this->em->getRepository('ArusBucketBundle:ArusBucket')->search( $data, $offset, $limit );

		return $t_bucket;
	}


	public function exist( $project, $name, $id=null, $return_object=false )
	{
		$t_params = ['project'=>$project, 'name'=>[$name,'=']];

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


	public function create( $project, $name )
	{
		$bucket = new ArusBucket();
		$bucket->setProject( $project );
		$bucket->setName( $name );

		$em = $this->em;
		$em->persist( $bucket );
		$em->flush( $bucket );

		return $bucket;
	}
	

	public function import( $project, $t_bucket )
	{
		set_time_limit( 0 );

		$em = $this->em;
		$container = $this->container;
		$cnt = 0;
		$t_bucket = array_map( 'trim', $t_bucket );

		foreach( $t_bucket as $b )
		{
			$bucket = $this->exist( $project, $b );
			if( $bucket ) {
				continue;
			}

			$this->create( $project, $b );
			$cnt++;
		}

		return $cnt;
	}
}
