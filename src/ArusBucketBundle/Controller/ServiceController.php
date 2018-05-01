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
		$t_params = ['project'=>$project, 'name'=>[$name,'LIKE']];
		//var_dump( $t_params );
		var_dump( $project->getId() );

		if( $id ) {
			$t_params['id'] = [$id,'!='];
		}
		
		if( $return_object ) {
			$offset = 1;
		} else {
			$offset = -1;
		}

		$exist = $this->search( $t_params, $offset );
		var_dump( $exist );

		if( !$return_object ) {
			return $exist;
		} elseif( is_array($exist) && count($exist) ) {
			return $exist[0];
		} else {
			return false;
		}
	}


	public function create( $project, $name, $set_acl=0, $get_acl=0, $read_api=0, $read_http=0, $write=0 )
	{
		$bucket = new ArusBucket();
		$bucket->setProject( $project );
		$bucket->setName( $name );
		$bucket->setPermSetACL( $set_acl );
		$bucket->setPermGetACL( $get_acl );
		$bucket->setPermReadAPI( $read_api );
		$bucket->setPermReadHTTP( $read_http );
		$bucket->setPermWrite( $write );

		$em = $this->em;
		$em->persist( $bucket );
		$em->flush( $bucket );

		return $bucket;
	}
	

	public function import( $project, $source_file )
	{
		if( !is_file($source_file) ) {
			return false;
		}
		
		$t_bucket = file( $source_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		
		return $this->doImport( $project, $t_bucket );
	}

	
	public function doImport( $project, $t_bucket, $t_perms=array() )
	{
		set_time_limit( 0 );

		$em = $this->em;
		$cnt = 0;
		$t_bucket = array_map( 'trim', $t_bucket );
		$t_bucket = array_unique( $t_bucket );

		foreach( $t_bucket as $b )
		{
			if( isset($t_perms[$b]) ) {
				$set_acl = $t_perms[$b][0];
				$get_acl = $t_perms[$b][1];
				$read_api = $t_perms[$b][2];
				$read_http = $t_perms[$b][3];
				$write = $t_perms[$b][4];
			} else {
				$set_acl = 0;
				$get_acl = 0;
				$read_api = 0;
				$read_http = 0;
				$write = 0;
			}

			$bucket = $this->exist( $project, $b, null, true );
			
			if( $bucket )
			{
				$bucket->setPermSetACL( $set_acl );
				$bucket->setPermGetACL( $get_acl );
				$bucket->setPermReadAPI( $read_api );
				$bucket->setPermReadHTTP( $read_http );
				$bucket->setPermWrite( $write );
				$em->persist( $bucket );
				$em->flush( $bucket );
			}
			else
			{
				$this->create( $project, $b, $set_acl, $get_acl, $read_api, $read_http, $write );
				$cnt++;				
			}
		}

		return $cnt;
	}
}
