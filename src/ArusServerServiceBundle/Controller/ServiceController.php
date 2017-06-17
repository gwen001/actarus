<?php

namespace ArusServerServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusServerBundle\Entity\ArusServer;
use ArusServerServiceBundle\Entity\ArusServerService;


class ServiceController extends Controller
{
	protected $em;

	protected $container;

	protected $router;

	protected $formFactory;

	protected $templating;


	public function __construct( $entityManager, $container, $router, $formFactory, $templating )
	{
		$this->em = $entityManager;
		$this->container = $container;
		$this->router = $router;
		$this->formFactory = $formFactory;
		$this->templating = $templating;
	}


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$t_service = $this->em->getRepository('ArusServerServiceBundle:ArusServerService')->search( $data, $offset, $limit );

		return $t_service;
	}

	
	public function create( $server, $port, $type, $service='', $version='' )
	{
		$ss = new ArusServerService();
		$ss->setServer( $server );
		$ss->setPort( (int)$port );
		$ss->setType( $type );
		$ss->setService( $service );
		$ss->setVersion( $version );

		$em = $this->em;
		$em->persist( $ss );
		$em->flush( $ss );

		return $ss;
	}
}
