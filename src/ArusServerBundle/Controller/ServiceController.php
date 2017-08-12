<?php

namespace ArusServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusServerBundle\Entity\ArusServer;
use ArusEntityTaskBundle\Entity\ArusEntityTask;

use Actarus\Utils;


class ServiceController extends Controller
{
	protected $em;

	protected $container;

	protected $router;

	protected $formFactory;

	protected $templating;


	public function __construct($entityManager, $container, $router, $formFactory, $templating)
	{
		$this->em = $entityManager;
		$this->container = $container;
		$this->router = $router;
		$this->formFactory = $formFactory;
		$this->templating = $templating;
	}


	public function search($data=[], $page = 1, $limit = -1)
	{
		if ($limit < 0) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page - 1);

		$t_server = $this->em->getRepository('ArusServerBundle:ArusServer')->search( $data, $offset, $limit );
		if( is_array($t_server) ) {
			foreach ($t_server as $s) {
				$this->get('app')->getRelatedEntityObject( $s, true );
			}
		}

		return $t_server;
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


	public function create( $project, $name, $recon=true )
	{
		if( !Utils::isIp($name) ) {
			return false;
		}

		$server = new ArusServer();
		$server->setProject( $project );
		$server->setName( $name, true );

		$em = $this->em;
		$em->persist( $server );
		$em->flush( $server );

		if( $recon ) {
			$this->get('app')->recon( $server, 'server' );
		}

		return $server;
	}


	public function import( $project, $t_server, $recon=true )
	{
		set_time_limit( 0 );

		$cnt = 0;
		$t_server = array_map( 'trim', $t_server );
		$t_server = array_unique( $t_server );

		foreach( $t_server as $s )
		{
			if( !Utils::isIp($s) ) {
				continue;
			}

			$exist = $this->exist( $project, $s );
			if( $exist ) {
				continue;
			}

			$this->create( $project, $s, $recon );
			$cnt++;
		}

		return $cnt;
	}
}
